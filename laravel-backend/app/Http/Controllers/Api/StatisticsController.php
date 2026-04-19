<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Measurement;
use App\Models\Station;
use App\Models\ClimateNormal;
use App\Models\YearlyAggregate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

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
     * Get climate normals (30-year averages) from database.
     */
    public function climateNormals(Request $request): JsonResponse
    {
        $request->validate([
            'period' => 'nullable|string|in:1961-1990,1971-2000,1981-2010,1991-2020',
            'station_ids' => 'nullable|array',
            'station_ids.*' => 'string|exists:stations,id',
        ]);

        $period = $request->get('period', '1991-2020');
        $stationIds = $request->get('station_ids'); // null = all stations

        // Query climate normals from database
        $query = ClimateNormal::where('reference_period_start', 1991)
            ->where('reference_period_end', 2020);

        if ($stationIds) {
            $query->whereIn('station_id', $stationIds);
        }

        $climateData = $query->with('station')->get();

        // Transform data to response format
        $stations = [];
        foreach ($climateData->groupBy('station_id') as $stId => $normals) {
            $station = Station::find($stId);
            if (!$station) continue;

            $monthlyData = [];
            $annualData = null;

            foreach ($normals as $normal) {
                if ($normal->month === 0) {
                    // Yearly average
                    $annualData = [
                        'temperature' => $normal->temp_mean,
                        'temp_max' => $normal->temp_max_mean,
                        'temp_min' => $normal->temp_min_mean,
                        'precipitation' => $normal->precipitation_mean,
                        'sunshine' => $normal->sunshine_hours_mean,
                    ];
                } else {
                    // Monthly data
                    $monthlyData[$normal->month] = [
                        'month' => $normal->month,
                        'temperature' => $normal->temp_mean,
                        'temp_max' => $normal->temp_max_mean,
                        'temp_min' => $normal->temp_min_mean,
                        'precipitation' => $normal->precipitation_mean,
                        'sunshine' => $normal->sunshine_hours_mean,
                    ];
                }
            }

            ksort($monthlyData);

            $stations[] = [
                'station_id' => $station->id,
                'station_name' => $station->name,
                'location' => $station->location,
                'elevation' => $station->elevation,
                'annual' => $annualData,
                'monthly' => array_values($monthlyData),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'period' => $period,
                'reference_period_start' => 1991,
                'reference_period_end' => 2020,
                'stations' => $stations,
                'parameters' => [
                    'temperature' => '°C',
                    'precipitation' => 'mm',
                    'sunshine' => 'hours',
                ]
            ],
            'meta' => [
                'station_count' => count($stations),
                'source' => 'climate_normals (database)',
                'updated_at' => now()->toIso8601String(),
            ]
        ]);
    }

    /**
     * Get trend analysis for a specific parameter using yearly aggregates.
     */
    public function trends(Request $request): JsonResponse
    {
        $request->validate([
            'parameter' => 'required|string|in:temperature,precipitation,sunshine',
            'station_id' => 'required|string|exists:stations,id',
            'start_year' => 'nullable|integer|min:1890|max:2026',
            'end_year' => 'nullable|integer|min:1890|max:2026',
        ]);

        $parameter = $request->get('parameter', 'temperature');
        $stationId = $request->get('station_id');
        $startYear = $request->get('start_year', 1990);
        $endYear = $request->get('end_year', 2024);

        // Validate station exists
        $station = Station::findOrFail($stationId);

        // Query yearly aggregates
        $yearlyData = YearlyAggregate::where('station_id', $stationId)
            ->whereBetween('year', [$startYear, $endYear])
            ->orderBy('year', 'asc')
            ->get();

        if ($yearlyData->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "No trend data available for station {$stationId} in period {$startYear}-{$endYear}",
                'meta' => ['station_id' => $stationId],
            ], 404);
        }

        // Extract values based on parameter
        $values = [];
        $years = [];

        foreach ($yearlyData as $year) {
            $years[] = $year->year;

            if ($parameter === 'temperature') {
                $values[] = $year->temp_mean;
            } elseif ($parameter === 'precipitation') {
                $values[] = $year->precipitation_sum;
            } elseif ($parameter === 'sunshine') {
                $values[] = $year->sunshine_hours;
            }
        }

        // Need at least 2 data points for trend analysis
        if (count($values) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient data for trend analysis (need at least 2 years)',
                'meta' => ['station_id' => $stationId, 'data_points' => count($values)],
            ], 422);
        }

        // Calculate trend line (simple linear regression)
        $n = count($values);
        $xSum = array_sum(array_keys($values));
        $ySum = array_sum($values);
        $xySum = 0;
        $x2Sum = 0;

        foreach ($values as $i => $y) {
            $x = $i;
            $xySum += $x * $y;
            $x2Sum += $x * $x;
        }

        // Avoid division by zero
        $denominator = ($n * $x2Sum - $xSum * $xSum);
        if ($denominator == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot calculate trend: insufficient variation in data',
                'meta' => ['station_id' => $stationId],
            ], 422);
        }

        $slope = ($n * $xySum - $xSum * $ySum) / $denominator;
        $intercept = ($ySum - $slope * $xSum) / $n;
        $ratePerDecade = $slope * 10; // Convert annual to decadal

        // Format annual values for response
        $annualValues = [];
        foreach ($years as $idx => $year) {
            $annualValues[$year] = round($values[$idx], 2);
        }

        // Calculate decadal averages
        $decadalAverages = [];
        foreach (
            $yearlyData->groupBy(function ($item) {
                return (intval($item->year / 10) * 10);
            }) as $decade => $items
        ) {
            if ($parameter === 'temperature') {
                $avg = $items->avg('temp_mean');
            } elseif ($parameter === 'precipitation') {
                $avg = $items->sum('precipitation_sum') / count($items);
            } elseif ($parameter === 'sunshine') {
                $avg = $items->sum('sunshine_hours') / count($items);
            }
            $decadalAverages["{$decade}s"] = round($avg, 2);
        }

        $trends = [
            'parameter' => $parameter,
            'parameter_unit' => $parameter === 'temperature' ? '°C' : ($parameter === 'precipitation' ? 'mm' : 'hours'),
            'station' => [
                'id' => $station->id,
                'name' => $station->name,
                'location' => $station->location,
            ],
            'period' => [
                'start_year' => $startYear,
                'end_year' => $endYear,
                'years' => $endYear - $startYear + 1,
            ],
            'analysis' => [
                'trend' => $slope > 0 ? 'increasing' : 'decreasing',
                'rate_per_year' => round($slope, 4),
                'rate_per_decade' => round($ratePerDecade, 4),
                'min_value' => round(min($values), 2),
                'max_value' => round(max($values), 2),
                'mean_value' => round(array_sum($values) / count($values), 2),
            ],
            'annual_values' => $annualValues,
            'decadal_averages' => $decadalAverages,
        ];

        return response()->json([
            'success' => true,
            'data' => $trends,
            'meta' => [
                'station_id' => $stationId,
                'source' => 'yearly_aggregates (database)',
                'records' => count($yearlyData),
                'analysis_date' => now()->toIso8601String(),
            ]
        ]);
    }

    /**
     * Get yearly aggregates for a station with optional year range.
     */
    public function yearlyAggregates(Request $request): JsonResponse
    {
        $request->validate([
            'station_id' => 'required|string|exists:stations,id',
            'start_year' => 'nullable|integer|min:1890|max:2026',
            'end_year' => 'nullable|integer|min:1890|max:2026',
            'order' => 'nullable|string|in:asc,desc',
        ]);

        $stationId = $request->get('station_id');
        $startYear = $request->get('start_year', 1990);
        $endYear = $request->get('end_year', now()->year);
        $order = $request->get('order', 'desc');

        $station = Station::find($stationId);
        if (!$station) {
            return response()->json([
                'success' => false,
                'message' => 'Station not found'
            ], 404);
        }

        // Get yearly aggregates
        $aggregates = YearlyAggregate::where('station_id', $stationId)
            ->whereBetween('year', [$startYear, $endYear])
            ->orderBy('year', $order)
            ->get();

        $data = [];
        foreach ($aggregates as $agg) {
            $data[] = [
                'year' => $agg->year,
                'temperature' => [
                    'max' => round($agg->temp_max_absolute, 1),
                    'min' => round($agg->temp_min_absolute, 1),
                    'mean' => round($agg->temp_mean, 1),
                ],
                'precipitation' => round($agg->precipitation_sum, 1),
                'sunshine' => round($agg->sunshine_hours, 1),
                'frost_days' => $agg->frost_days,
                'summer_days' => $agg->summer_days,
                'rainy_days' => $agg->rainy_days,
                'snowy_days' => $agg->snowy_days,
                'snow_depth_max' => round($agg->snow_depth_max, 1),
                'records_count' => $agg->records_count,
                'valid_records' => $agg->valid_records,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'station' => [
                    'id' => $station->id,
                    'name' => $station->name,
                    'location' => $station->location,
                ],
                'period' => [
                    'start_year' => $startYear,
                    'end_year' => $endYear,
                ],
                'aggregates' => $data,
            ],
            'meta' => [
                'station_id' => $stationId,
                'records' => count($data),
                'source' => 'yearly_aggregates (database)',
                'updated_at' => now()->toIso8601String(),
            ]
        ]);
    }

    /**
     * Get monthly aggregates for a station and year.
     */
    public function monthlyAggregates(Request $request): JsonResponse
    {
        $request->validate([
            'station_id' => 'required|string|exists:stations,id',
            'year' => 'required|integer|min:1890|max:2026',
        ]);

        $stationId = $request->get('station_id');
        $year = $request->get('year');

        $station = Station::find($stationId);
        if (!$station) {
            return response()->json([
                'success' => false,
                'message' => 'Station not found'
            ], 404);
        }

        // Get monthly aggregates
        $aggregates = DB::table('monthly_aggregates')
            ->where('station_id', $stationId)
            ->where('year', $year)
            ->orderBy('month', 'asc')
            ->get();

        $months = [
            'Januar',
            'Februar',
            'März',
            'April',
            'Mai',
            'Juni',
            'Juli',
            'August',
            'September',
            'Oktober',
            'November',
            'Dezember'
        ];

        $data = [];
        foreach ($aggregates as $agg) {
            $monthIdx = (int)$agg->month - 1;
            $data[] = [
                'month' => (int)$agg->month,
                'month_name' => $months[$monthIdx] ?? 'Unknown',
                'temperature' => [
                    'max' => round($agg->temp_max_absolute, 1),
                    'min' => round($agg->temp_min_absolute, 1),
                    'mean' => round($agg->temp_mean, 1),
                ],
                'precipitation' => round($agg->precipitation_sum, 1),
                'sunshine' => round($agg->sunshine_hours, 1),
                'frost_days' => (int)$agg->frost_days,
                'summer_days' => (int)$agg->summer_days,
                'rainy_days' => (int)$agg->rainy_days,
                'snowy_days' => (int)$agg->snowy_days,
                'records_count' => (int)$agg->records_count,
                'valid_records' => (int)$agg->valid_records,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'station' => [
                    'id' => $station->id,
                    'name' => $station->name,
                    'location' => $station->location,
                ],
                'year' => $year,
                'aggregates' => $data,
            ],
            'meta' => [
                'station_id' => $stationId,
                'records' => count($data),
                'source' => 'monthly_aggregates (database)',
                'updated_at' => now()->toIso8601String(),
            ]
        ]);
    }

    /**
     * Get rankings for stations or years.
     */
    public function rankings(Request $request): JsonResponse
    {
        $request->validate([
            'station_id' => 'nullable|string|exists:stations,id',
            'metric' => 'required|string|in:warmest_year,coldest_year,wettest_year,driest_year,sunniest_year,most_frosts,most_summer_days',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $stationId = $request->get('station_id');
        $metric = $request->get('metric', 'warmest_year');
        $limit = $request->get('limit', 10);

        $query = YearlyAggregate::query();

        if ($stationId) {
            $query->where('station_id', $stationId);
        }

        // Apply sorting based on metric
        $results = match ($metric) {
            'warmest_year' => $query->orderBy('temp_mean', 'desc')->limit($limit)->get(),
            'coldest_year' => $query->orderBy('temp_mean', 'asc')->limit($limit)->get(),
            'wettest_year' => $query->orderBy('precipitation_sum', 'desc')->limit($limit)->get(),
            'driest_year' => $query->orderBy('precipitation_sum', 'asc')->limit($limit)->get(),
            'sunniest_year' => $query->orderBy('sunshine_hours', 'desc')->limit($limit)->get(),
            'most_frosts' => $query->orderBy('frost_days', 'desc')->limit($limit)->get(),
            'most_summer_days' => $query->orderBy('summer_days', 'desc')->limit($limit)->get(),
            default => $query->orderBy('temp_mean', 'desc')->limit($limit)->get(),
        };

        $data = [];
        foreach ($results as $idx => $result) {
            $station = Station::find($result->station_id);
            $data[] = [
                'rank' => $idx + 1,
                'year' => $result->year,
                'station_id' => $result->station_id,
                'station_name' => $station?->name ?? 'Unknown',
                'temperature_mean' => round($result->temp_mean, 1),
                'temperature_max' => round($result->temp_max_absolute, 1),
                'temperature_min' => round($result->temp_min_absolute, 1),
                'precipitation_sum' => round($result->precipitation_sum, 1),
                'sunshine_hours' => round($result->sunshine_hours, 1),
                'frost_days' => $result->frost_days,
                'summer_days' => $result->summer_days,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'metric' => $metric,
                'station_id' => $stationId,
                'limit' => $limit,
                'rankings' => $data,
            ],
            'meta' => [
                'records' => count($data),
                'source' => 'yearly_aggregates (database)',
                'updated_at' => now()->toIso8601String(),
            ]
        ]);
    }
}
