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
        // For now, return mock statistics
        // In production, this would query the database
        
        $statistics = [
            'stations' => [
                'total' => 16,
                'active' => 16,
                'by_state' => [
                    'Berlin' => 1,
                    'Bremen' => 1,
                    'Sachsen' => 2,
                    'NRW' => 4,
                    'Hessen' => 1,
                    'Hamburg' => 1,
                    'Niedersachsen' => 1,
                    'Baden-Württemberg' => 2,
                    'Bayern' => 2,
                    'Mecklenburg-Vorpommern' => 1,
                    'Saarland' => 1,
                ]
            ],
            'measurements' => [
                'total' => 27405,
                'daily_average' => 2.3,
                'by_year' => [
                    '2024' => 365,
                    '2023' => 365,
                    '2022' => 365,
                    '2021' => 365,
                    '2020' => 366, // leap year
                ]
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
                'start' => '1990-01-01',
                'end' => '2024-12-31',
                'years' => 35,
                'days' => 12784,
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
        
        // For now, return mock statistics
        // In production, this would query the database
        
        $statistics = [
            'station' => [
                'id' => $station->id,
                'name' => $station->name,
                'location' => $station->location,
                'elevation' => $station->elevation,
                'state' => $station->state,
            ],
            'measurements' => [
                'total' => 12450,
                'start_date' => '1990-01-01',
                'end_date' => '2024-12-31',
                'years' => 35,
                'completeness' => 95.8,
            ],
            'temperature' => [
                'mean' => 9.8,
                'min' => -15.2,
                'max' => 38.7,
                'avg_summer' => 18.5,
                'avg_winter' => 1.2,
            ],
            'precipitation' => [
                'annual_mean' => 789,
                'max_daily' => 85.3,
                'rainy_days_per_year' => 165,
                'snow_days_per_year' => 25,
            ],
            'sunshine' => [
                'annual_mean' => 1845,
                'max_daily' => 16.2,
                'sunny_days_per_year' => 85,
            ],
            'extremes' => [
                'hottest_day' => [
                    'date' => '2019-07-25',
                    'temperature' => 38.7,
                ],
                'coldest_day' => [
                    'date' => '1991-02-14',
                    'temperature' => -15.2,
                ],
                'wettest_day' => [
                    'date' => '2002-08-12',
                    'precipitation' => 85.3,
                ],
                'sunniest_day' => [
                    'date' => '2018-06-21',
                    'sunshine' => 16.2,
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
