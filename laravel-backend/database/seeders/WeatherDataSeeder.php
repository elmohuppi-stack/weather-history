<?php

namespace Database\Seeders;

use App\Models\Station;
use App\Models\Measurement;
use App\Models\ClimateNormal;
use App\Models\MonthlyAggregate;
use App\Models\YearlyAggregate;
use Illuminate\Database\Seeder;

class WeatherDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample stations
        $stations = [
            [
                'id' => '01048',
                'name' => 'Berlin-Tempelhof',
                'location' => 'Berlin',
                'state' => 'Berlin',
                'lat' => 52.4745,
                'lon' => 13.4023,
                'elevation' => 48,
                'start_year' => 1960,
                'measurement_count' => 24000,
                'active' => true,
                'latest_date' => now()->subDays(1),
                'dwd_url' => 'https://www.dwd.de',
            ],
            [
                'id' => '01050',
                'name' => 'München-Flughafen',
                'location' => 'Munich',
                'state' => 'Bavaria',
                'lat' => 48.3519,
                'lon' => 11.7850,
                'elevation' => 446,
                'start_year' => 1980,
                'measurement_count' => 16000,
                'active' => true,
                'latest_date' => now()->subDays(1),
                'dwd_url' => 'https://www.dwd.de',
            ],
            [
                'id' => '01110',
                'name' => 'Borkum',
                'location' => 'Borkum',
                'state' => 'Lower Saxony',
                'lat' => 53.5886,
                'lon' => 6.6656,
                'elevation' => 5,
                'start_year' => 1952,
                'measurement_count' => 12000,
                'active' => true,
                'latest_date' => now()->subDays(1),
                'dwd_url' => 'https://www.dwd.de',
            ],
        ];

        foreach ($stations as $stationData) {
            Station::updateOrCreate(
                ['id' => $stationData['id']],
                $stationData
            );
        }

        // Create sample measurements for Berlin (2020-2024, last 5 years)
        $berlinStation = Station::find('01048');
        $startDate = \Carbon\Carbon::parse('2020-01-01');
        $endDate = now()->subDays(1);

        // Clear existing measurements for these stations
        Measurement::whereIn('station_id', ['01048', '01050', '01110'])->delete();

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            // Realistic weather patterns based on month
            $month = $date->month;
            $dayOfYear = $date->dayOfYear;
            
            // Temperature variations by season
            $baseTemp = 8 + 15 * sin(2 * M_PI * ($dayOfYear - 80) / 365); // Seasonal curve
            $tempNoise = (rand(-50, 50) / 100); // ±0.5°C random
            $tempMean = $baseTemp + $tempNoise;

            Measurement::create([
                'station_id' => '01048',
                'date' => $date->format('Y-m-d'),
                'temp_max' => $tempMean + 2 + (rand(-20, 30) / 100),
                'temp_min' => $tempMean - 2 + (rand(-20, 30) / 100),
                'temp_mean' => $tempMean,
                'precipitation' => $month >= 5 && $month <= 9 ? rand(0, 150) / 10 : rand(0, 80) / 10,
                'sunshine' => max(0, 5 + 10 * sin(2 * M_PI * ($dayOfYear - 80) / 365) + (rand(-30, 30) / 10)),
                'snow_depth' => $month >= 11 || $month <= 2 ? rand(0, 100) / 10 : 0,
            ]);
        }

        // Create sample measurements for Munich
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $month = $date->month;
            $dayOfYear = $date->dayOfYear;
            
            // Munich is cooler and has more snow in winter
            $baseTemp = 7 + 16 * sin(2 * M_PI * ($dayOfYear - 80) / 365) - 1.5; // Slightly cooler
            $tempNoise = (rand(-50, 50) / 100);
            $tempMean = $baseTemp + $tempNoise;

            Measurement::create([
                'station_id' => '01050',
                'date' => $date->format('Y-m-d'),
                'temp_max' => $tempMean + 2.5 + (rand(-20, 30) / 100),
                'temp_min' => $tempMean - 2.5 + (rand(-20, 30) / 100),
                'temp_mean' => $tempMean,
                'precipitation' => $month >= 6 && $month <= 8 ? rand(0, 180) / 10 : rand(0, 100) / 10,
                'sunshine' => max(0, 4 + 11 * sin(2 * M_PI * ($dayOfYear - 80) / 365) + (rand(-30, 30) / 10)),
                'snow_depth' => $month >= 11 || $month <= 2 ? rand(0, 200) / 10 : 0,
            ]);
        }

        // Create sample measurements for Borkum (coastal)
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $month = $date->month;
            $dayOfYear = $date->dayOfYear;
            
            // Borkum is coastal, milder, windier
            $baseTemp = 9 + 14 * sin(2 * M_PI * ($dayOfYear - 80) / 365) - 0.5; // Milder
            $tempNoise = (rand(-50, 50) / 100);
            $tempMean = $baseTemp + $tempNoise;

            Measurement::create([
                'station_id' => '01110',
                'date' => $date->format('Y-m-d'),
                'temp_max' => $tempMean + 1.5 + (rand(-20, 30) / 100),
                'temp_min' => $tempMean - 1.5 + (rand(-20, 30) / 100),
                'temp_mean' => $tempMean,
                'precipitation' => rand(10, 200) / 10, // Higher precipitation year-round
                'sunshine' => max(0, 3 + 9 * sin(2 * M_PI * ($dayOfYear - 80) / 365) + (rand(-30, 30) / 10)),
                'snow_depth' => $month >= 11 || $month <= 2 ? rand(0, 50) / 10 : 0,
            ]);
        }

        echo "Sample data seeded successfully!\n";
    }
}
