<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Measurement;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StatisticsController extends Controller
{
    /**
     * Get overall statistics for all stations.
     */
    public function overall(): JsonResponse
    {
        // Get real statistics from database
        
        // Station statistics
        $stationCount = Station::count();
        $activeStationCount = Station::where('active', true)->count();
        $stationsByState = Station::selectRaw('state, COUNT(*) as count')
            ->groupBy('state')
            ->orderBy('count', 'desc')
            ->get()
            ->pluck('count', 'state')
            ->toArray();
        
        // Measurement statistics
        $measurementCount = Measurement::count();
        $dateRange = Measurement::selectRaw('MIN(date) as start_date, MAX(date) as end_date')
            ->first();
        
        $daysDiff = 0;
        $yearsDiff = 0;
        if ($dateRange->start_date && $dateRange->end_date) {
            $start = new \DateTime($dateRange->start_date);
            $end = new \DateTime($dateRange->end_date);
            $daysDiff = $start->diff($end)->days + 1;
            $yearsDiff = round($daysDiff / 365.25, 1);
        }
        
        $dailyAverage = $daysDiff > 0 ? round($measurementCount / $daysDiff, 1) : 0;
        
        // Get measurements by year
        $measurementsByYear = Measurement::selectRaw('EXTRACT(YEAR FROM date) as year, COUNT(*) as count')
            ->groupByRaw('EXTRACT(YEAR FROM date)')
            ->orderBy('year', 'desc')
            ->limit(5)
            ->get()
            ->pluck('count', 'year')
            ->toArray();
        
        $statistics = [
            'stations' => [
                'total' => $stationCount,
                'active' => $activeStationCount,
                'by_state' => $stationsByState,
            ],
            'measurements' => [
                'total' => $measurementCount,
                'daily_average' => $dailyAverage,
                'by_year' => $measurementsByYear,
            ],
            'parameters' => [
                'total' => 8,
                'list' => [
                    'temp_max' => 'Maximale Temperatur',
                    'temp_min' => 'Minimale Temperatur',
                    'temp_mean' => 'Mittlere Temperatur',
                    'precipitation' => 'Niederschlag',
                    'sunshine' => 'Sonnenscheindauer',
                    'snow_depth' => 'Schneehöhe',
                    'cloud_cover' => 'Bewölkung',
                    'wind_speed' => 'Windgeschwindigkeit',
                ]
            ],
            'time_range' => [
                'start' => $dateRange->start_date ?? '1990-01-01',
                'end' => $dateRange->end_date ?? '2024-12-31',
                'years' => $yearsDiff,
                'days' => $daysDiff,
            ]
        ];
        
        return response()->json([
            'success' => true,
            'data' => $statistics,
            'meta' => [
                'updated_at' => now()->toIso8601String(),
            ]
        ]);
    }

    /**
     * Get statistics for a specific station.
     */
    public function station(string $stationId): JsonResponse
    {
        // Check if station exists
        $station = Station::find($stationId);
        
        if (!$station) {
            return response()->json([
                'success' => false,
                'message' => 'Station not found'
            ], 404);
        }
        
        // Get real statistics from database
        $measurementStats = Measurement::where('station_id', $stationId)
            ->selectRaw('COUNT(*) as total, MIN(date) as start_date, MAX(date) as end_date')
            ->first();
        
        $daysDiff = 0;
        $yearsDiff = 0;
        $completeness = 0;
        
        if ($measurementStats->start_date && $measurementStats->end_date) {
            $start = new \DateTime($measurementStats->start_date);
            $end = new \DateTime($measurementStats->end_date);
            $daysDiff = $start->diff($end)->days + 1;
            $yearsDiff = round($daysDiff / 365.25, 1);
            $completeness = $daysDiff > 0 ? round(($measurementStats->total / $daysDiff) * 100, 1) : 0;
        }
        
        // Temperature statistics
        $tempStats = Measurement::where('station_id', $stationId)
            ->whereNotNull('temp_mean')
            ->selectRaw('
                AVG(temp_mean) as mean,
                MIN(temp_min) as min,
                MAX(temp_max) as max,
                AVG(CASE WHEN EXTRACT(MONTH FROM date) IN (6,7,8) THEN temp_mean END) as avg_summer,
                AVG(CASE WHEN EXTRACT(MONTH FROM date) IN (12,1,2) THEN temp_mean END) as avg_winter
            ')
            ->first();
        
        // Precipitation statistics
        $precipStats = Measurement::where('station_id', $stationId)
            ->whereNotNull('precipitation')
            ->selectRaw('
                AVG(precipitation) * 365 as annual_mean,
                MAX(precipitation) as max_daily,
                COUNT(CASE WHEN precipitation > 0.1 THEN 1 END) as rainy_days,
                COUNT(CASE WHEN snow_depth > 0 THEN 1 END) as snow_days
            ')
            ->first();
        
        // Sunshine statistics
        $sunshineStats = Measurement::where('station_id', $stationId)
            ->whereNotNull('sunshine')
            ->selectRaw('
                AVG(sunshine) * 365 as annual_mean,
                MAX(sunshine) as max_daily,
                COUNT(CASE WHEN sunshine > 6 THEN 1 END) as sunny_days
            ')
            ->first();
        
        // Extremes
        $hottestDay = Measurement::where('station_id', $stationId)
            ->whereNotNull('temp_max')
            ->orderBy('temp_max', 'desc')
            ->first(['date', 'temp_max']);
        
        $coldestDay = Measurement::where('station_id', $stationId)
            ->whereNotNull('temp_min')
            ->orderBy('temp_min', 'asc')
            ->first(['date', 'temp_min']);
        
        $wettestDay = Measurement::where('station_id', $stationId)
            ->whereNotNull('precipitation')
            ->orderBy('precipitation', 'desc')
            ->first(['date', 'precipitation']);
        
        $sunniestDay = Measurement::where('station_id', $stationId)
            ->whereNotNull('sunshine')
            ->orderBy('sunshine', 'desc')
            ->first(['date', 'sunshine']);
        
        $statistics = [
            'station' => [
                'id' => $station->id,
                'name' => $station->name,
                'location' => $station->location,
                'elevation' => $station->elevation,
                'state' => $station->state,
            ],
            'measurements' => [
                'total' => $measurementStats->total ?? 0,
                'start_date' => $measurementStats->start_date ?? null,
                'end_date' => $measurementStats->end_date ?? null,
                'years' => $yearsDiff,
                'completeness' => $completeness,
            ],
            'temperature' => [
                'mean' => round($tempStats->mean ?? 0, 1),
                'min' => round($tempStats->min ?? 0, 1),
                'max' => round($tempStats->max ?? 0, 1),
                'avg_summer' => round($tempStats->avg_summer ?? 0, 1),
                'avg_winter' => round($tempStats->avg_winter ?? 0, 1),
            ],
            'precipitation' => [
                'annual_mean' => round($precipStats->annual_mean ?? 0, 0),
                'max_daily' => round($precipStats->max_daily ?? 0, 1),
                'rainy_days_per_year' => $daysDiff > 0 ? round(($precipStats->rainy_days ?? 0) / $yearsDiff, 0) : 0,
                'snow_days_per_year' => $daysDiff > 0 ? round(($precipStats->snow_days ?? 0) / $yearsDiff, 0) : 0,
            ],
            'sunshine' => [
                'annual_mean' => round($sunshineStats->annual_mean ?? 0, 0),
                'max_daily' => round($sunshineStats->max_daily ?? 0, 1),
                'sunny_days_per_year' => $daysDiff > 0 ? round(($sunshineStats->sunny_days ?? 0) / $yearsDiff, 0) : 0,
            ],
            'extremes' => [
                'hottest_day' => [
                    'date' => $hottestDay->date ?? null,
                    'temperature' => round($hottestDay->temp_max ?? 0, 1),
                ],
                'coldest_day' => [
                    'date' => $coldestDay->date ?? null,
                    'temperature' => round($coldestDay->temp_min ?? 0, 1),
                ],
                'wettest_day' => [
                    'date' => $wettestDay->date ?? null,
                    'precipitation' => round($wettestDay->precipitation ?? 0, 1),
                ],
                'sunniest_day' => [
                    'date' => $sunniestDay->date ?? null,
                    'sunshine' => round($sunniestDay->sunshine ?? 0, 1),
                ],
            ]
        ];
        
        return response()->json([
            'success' => true,
            'data' => $statistics,
            'meta' => [
                'station_id' => $stationId,
                'updated_at' => now()->toIso8601String(),
            ]
        ]);
    }

    /**
     * Get climate normals (30-year averages).
     */
    public function climateNormals(Request $request): JsonResponse
    {
        $request->validate([
            'period' => 'nullable|string|in:1961-1990,1971-2000,1981-2010,1991-2020',
            'station_ids' => 'nullable|array',
            'station_ids.*' => 'string|exists:stations,id',
        ]);
        
        $period = $request->get('period', '1991-2020');
        
        // For now, return mock climate normals
        // In production, this would query the database
        
        $normals = [
            'period' => $period,
            'stations' => [
                [
                    'station_id' => '01048',
                    'station_name' => 'Berlin-Tempelhof',
                    'temperature' => [
                        'annual' => 9.8,
                        'january' => 0.5,
                        'july' => 19.2,
                    ],
                    'precipitation' => [
                        'annual' => 589,
                        'summer' => 185,
                        'winter' => 125,
                    ],
                    'sunshine' => [
                        'annual' => 1645,
                        'summer' => 685,
                        'winter' => 145,
                    ],
                ],
                [
                    'station_id' => '01001',
                    'station_name' => 'Bremen',
                    'temperature' => [
                        'annual' => 9.5,
                        'january' => 1.2,
                        'july' => 18.8,
                    ],
                    'precipitation' => [
                        'annual' => 732,
                        'summer' => 215,
                        'winter' => 165,
                    ],
                    'sunshine' => [
                        'annual' => 1542,
                        'summer' => 625,
                        'winter' => 125,
                    ],
                ],
            ],
            'parameters' => [
                'temperature' => '°C',
                'precipitation' => 'mm',
                'sunshine' => 'hours',
            ]
        ];
        
        return response()->json([
            'success' => true,
            'data' => $normals,
            'meta' => [
                'period' => $period,
                'station_count' => count($normals['stations']),
                'updated_at' => now()->toIso8601String(),
            ]
        ]);
    }

    /**
     * Get trend analysis for a specific parameter.
     */
    public function trends(Request $request): JsonResponse
    {
        $request->validate([
            'parameter' => 'required|string|in:temperature,precipitation,sunshine',
            'station_id' => 'nullable|string|exists:stations,id',
            'start_year' => 'nullable|integer|min:1990|max:2024',
            'end_year' => 'nullable|integer|min:1990|max:2024',
        ]);
        
        $parameter = $request->get('parameter', 'temperature');
        $stationId = $request->get('station_id');
        $startYear = $request->get('start_year', 1990);
        $endYear = $request->get('end_year', 2024);
        
        // For now, return mock trend data
        // In production, this would query the database
        
        $trends = [
            'parameter' => $parameter,
            'station_id' => $stationId,
            'period' => [
                'start_year' => $startYear,
                'end_year' => $endYear,
                'years' => $endYear - $startYear + 1,
            ],
            'analysis' => [
                'trend' => 'increasing',
                'rate_per_decade' => 0.35,
                'significance' => 'high',
                'r_squared' => 0.78,
            ],
            'annual_values' => [
                '1990' => 8.9,
                '1995' => 9.1,
                '2000' => 9.4,
                '2005' => 9.6,
                '2010' => 9.8,
                '2015' => 10.1,
                '2020' => 10.3,
                '2024' => 10.5,
            ],
            'decadal_averages' => [
                '1990s' => 9.2,
                '2000s' => 9.7,
                '2010s' => 10.0,
                '2020s' => 10.4,
            ]
        ];
        
        return response()->json([
            'success' => true,
            'data' => $trends,
            'meta' => [
                'parameter' => $parameter,
                'analysis_date' => now()->toIso8601String(),
            ]
        ]);
    }
}
