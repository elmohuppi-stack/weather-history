<?php

namespace App\Console\Commands;

use App\Models\Measurement;
use App\Models\MonthlyAggregate;
use App\Models\YearlyAggregate;
use App\Models\ClimateNormal;
use App\Models\Station;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ComputeWeatherAggregates extends Command
{
    protected $signature = 'weather:compute-aggregates {--station= : Compute only for specific station ID} {--year= : Compute only for specific year}';
    protected $description = 'Compute monthly and yearly aggregates from daily measurements';

    public function handle(): int
    {
        $stationId = $this->option('station');
        $year = $this->option('year');

        $this->info('Computing weather aggregates...');

        // Bestimme Stationen zum Verarbeiten
        if ($stationId) {
            $stations = Station::where('id', $stationId)->get();
            if ($stations->isEmpty()) {
                $this->error("Station $stationId not found");
                return 1;
            }
        } else {
            $stations = Station::all();
        }

        $bar = $this->output->createProgressBar($stations->count());

        foreach ($stations as $station) {
            $this->computeMonthlyAggregates($station, $year);
            $this->computeYearlyAggregates($station, $year);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Berechne Klima-Normen (1991-2020)
        $this->info('Computing climate normals (1991-2020)...');
        $this->computeClimateNormals();

        $this->info('✓ Aggregates computed successfully');
        return 0;
    }

    private function computeMonthlyAggregates(Station $station, ?string $year = null): void
    {
        // Bestimme Jahr-Range
        if ($year) {
            $years = [(int)$year];
        } else {
            $years = Measurement::where('station_id', $station->id)
                ->selectRaw('EXTRACT(YEAR FROM date)::integer as year')
                ->distinct()
                ->orderBy('year')
                ->pluck('year')
                ->toArray();
        }

        foreach ($years as $y) {
            for ($m = 1; $m <= 12; $m++) {
                $startDate = (int)$y . "-" . str_pad($m, 2, '0', STR_PAD_LEFT) . "-01";
                $endDate = date('Y-m-t', strtotime($startDate));

                // Hole tägliche Daten für diesen Monat
                $measurements = Measurement::where('station_id', $station->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->get();

                if ($measurements->isEmpty()) {
                    continue;
                }

                // Berechne Aggregate
                $aggregate = [
                    'station_id' => $station->id,
                    'year' => (int)$y,
                    'month' => $m,
                    'temp_max_absolute' => $measurements->max('temp_max'),
                    'temp_min_absolute' => $measurements->min('temp_min'),
                    'temp_mean' => round($measurements->average('temp_mean'), 2),
                    'precipitation_sum' => round($measurements->sum('precipitation') ?? 0, 1),
                    'sunshine_hours' => round($measurements->sum('sunshine') ?? 0, 1),
                    'snow_depth_max' => $measurements->max('snow_depth'),
                    'frost_days' => $measurements->where('temp_min', '<', 0)->count(),
                    'summer_days' => $measurements->where('temp_max', '>', 25)->count(),
                    'rainy_days' => $measurements->where('precipitation', '>', 0.1)->count(),
                    'snowy_days' => $measurements->where('snow_depth', '>', 0)->count(),
                    'records_count' => $measurements->count(),
                    'valid_records' => $measurements->whereNotNull('temp_mean')->count(),
                ];

                // Speichere oder aktualisiere Aggregate
                MonthlyAggregate::updateOrCreate(
                    ['station_id' => $station->id, 'year' => (int)$y, 'month' => $m],
                    $aggregate
                );
            }
        }
    }

    private function computeYearlyAggregates(Station $station, ?string $year = null): void
    {
        // Bestimme Jahr-Range
        if ($year) {
            $years = [(int)$year];
        } else {
            $years = Measurement::where('station_id', $station->id)
                ->selectRaw('EXTRACT(YEAR FROM date)::integer as year')
                ->distinct()
                ->orderBy('year')
                ->pluck('year')
                ->toArray();
        }

        foreach ($years as $y) {
            $startDate = (int)$y . "-01-01";
            $endDate = (int)$y . "-12-31";

            // Hole tägliche Daten für dieses Jahr
            $measurements = Measurement::where('station_id', $station->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            if ($measurements->isEmpty()) {
                continue;
            }

            // Berechne Aggregate
            $aggregate = [
                'station_id' => $station->id,
                'year' => (int)$y,
                'temp_max_absolute' => $measurements->max('temp_max'),
                'temp_min_absolute' => $measurements->min('temp_min'),
                'temp_mean' => round($measurements->average('temp_mean'), 2),
                'precipitation_sum' => round($measurements->sum('precipitation') ?? 0, 1),
                'sunshine_hours' => round($measurements->sum('sunshine') ?? 0, 1),
                'snow_depth_max' => $measurements->max('snow_depth'),
                'frost_days' => $measurements->where('temp_min', '<', 0)->count(),
                'summer_days' => $measurements->where('temp_max', '>', 25)->count(),
                'rainy_days' => $measurements->where('precipitation', '>', 0.1)->count(),
                'snowy_days' => $measurements->where('snow_depth', '>', 0)->count(),
                'records_count' => $measurements->count(),
                'valid_records' => $measurements->whereNotNull('temp_mean')->count(),
            ];

            // Speichere oder aktualisiere Aggregate
            YearlyAggregate::updateOrCreate(
                ['station_id' => $station->id, 'year' => (int)$y],
                $aggregate
            );
        }
    }

    private function computeClimateNormals(): void
    {
        // Berechne Klima-Normen für 1991-2020 (Standard 30-Jahres-Periode)
        $startYear = 1991;
        $endYear = 2020;

        $stations = Station::all();

        foreach ($stations as $station) {
            // Monatliche Normen
            for ($m = 1; $m <= 12; $m++) {
                $measurements = Measurement::where('station_id', $station->id)
                    ->whereRaw("EXTRACT(MONTH FROM date) = ?", [$m])
                    ->whereRaw("EXTRACT(YEAR FROM date) BETWEEN ? AND ?", [$startYear, $endYear])
                    ->get();

                if (!$measurements->isEmpty()) {
                    $normal = [
                        'station_id' => $station->id,
                        'month' => $m,
                        'temp_mean' => round($measurements->average('temp_mean'), 2),
                        'temp_max_mean' => round($measurements->average('temp_max'), 2),
                        'temp_min_mean' => round($measurements->average('temp_min'), 2),
                        'precipitation_mean' => round($measurements->average('precipitation') ?? 0, 1),
                        'sunshine_hours_mean' => round($measurements->average('sunshine') ?? 0, 1),
                        'reference_period_start' => $startYear,
                        'reference_period_end' => $endYear,
                    ];

                    ClimateNormal::updateOrCreate(
                        ['station_id' => $station->id, 'month' => $m],
                        $normal
                    );
                }
            }

            // Jahres-Normal (month = 0)
            $measurements = Measurement::where('station_id', $station->id)
                ->whereRaw("EXTRACT(YEAR FROM date) BETWEEN ? AND ?", [$startYear, $endYear])
                ->get();

            if (!$measurements->isEmpty()) {
                $normal = [
                    'station_id' => $station->id,
                    'month' => 0,
                    'temp_mean' => round($measurements->average('temp_mean'), 2),
                    'temp_max_mean' => round($measurements->average('temp_max'), 2),
                    'temp_min_mean' => round($measurements->average('temp_min'), 2),
                    'precipitation_mean' => round($measurements->average('precipitation') ?? 0, 1),
                    'sunshine_hours_mean' => round($measurements->average('sunshine') ?? 0, 1),
                    'reference_period_start' => $startYear,
                    'reference_period_end' => $endYear,
                ];

                ClimateNormal::updateOrCreate(
                    ['station_id' => $station->id, 'month' => 0],
                    $normal
                );
            }
        }
    }
}
