<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StationController extends Controller
{
    /**
     * Get all stations
     */
    public function index(Request $request): JsonResponse
    {
        // For now, return mock data matching the frontend
        $stations = [
            [
                'id' => '01048',
                'name' => 'Berlin-Tempelhof',
                'location' => 'Berlin, Deutschland',
                'elevation' => 48,
                'start_year' => 1990,
                'measurement_count' => 12450,
                'state' => 'Berlin',
                'latest_date' => '2024-12-31',
                'active' => true,
                'lat' => 52.4828,
                'lon' => 13.3893
            ],
            [
                'id' => '01001',
                'name' => 'Bremen',
                'location' => 'Bremen, Deutschland',
                'elevation' => 4,
                'start_year' => 1990,
                'measurement_count' => 12450,
                'state' => 'Bremen',
                'latest_date' => '2024-12-31',
                'active' => true,
                'lat' => 53.0793,
                'lon' => 8.8017
            ],
            [
                'id' => '01072',
                'name' => 'Dresden-Klotzsche',
                'location' => 'Dresden, Deutschland',
                'elevation' => 227,
                'start_year' => 1990,
                'measurement_count' => 12450,
                'state' => 'Sachsen',
                'latest_date' => '2024-12-31',
                'active' => true,
                'lat' => 51.1278,
                'lon' => 13.7542
            ],
            [
                'id' => '01078',
                'name' => 'Düsseldorf',
                'location' => 'Düsseldorf, Deutschland',
                'elevation' => 44,
                'start_year' => 1990,
                'measurement_count' => 12450,
                'state' => 'NRW',
                'latest_date' => '2024-12-31',
                'active' => true,
                'lat' => 51.2277,
                'lon' => 6.7735
            ],
            [
                'id' => '01091',
                'name' => 'Essen',
                'location' => 'Essen, Deutschland',
                'elevation' => 161,
                'start_year' => 1990,
                'measurement_count' => 12450,
                'state' => 'NRW',
                'latest_date' => '2024-12-31',
                'active' => true,
                'lat' => 51.4556,
                'lon' => 7.0116
            ],
            [
                'id' => '01420',
                'name' => 'Frankfurt/Main',
                'location' => 'Frankfurt, Deutschland',
                'elevation' => 112,
                'start_year' => 1990,
                'measurement_count' => 12450,
                'state' => 'Hessen',
                'latest_date' => '2024-12-31',
                'active' => true,
                'lat' => 50.1109,
                'lon' => 8.6821
            ],
            [
                'id' => '01358',
                'name' => 'Hamburg-Fuhlsbüttel',
                'location' => 'Hamburg, Deutschland',
                'elevation' => 16,
                'start_year' => 1990,
                'measurement_count' => 12450,
                'state' => 'Hamburg',
                'latest_date' => '2024-12-31',
                'active' => true,
                'lat' => 53.6304,
                'lon' => 10.0082
            ],
            [
                'id' => '01103',
                'name' => 'Hannover',
                'location' => 'Hannover, Deutschland',
                'elevation' => 55,
                'start_year' => 1990,
                'measurement_count' => 12450,
                'state' => 'Niedersachsen',
                'latest_date' => '2024-12-31',
                'active' => true,
                'lat' => 52.3759,
                'lon' => 9.7320
            ],
            [
                'id' => '01427',
                'name' => 'Karlsruhe-Rheinstetten',
                'location' => 'Karlsruhe, Deutschland',
                'elevation' => 112,
                'start_year' => 1990,
                'measurement_count' => 12450,
                'state' => 'Baden-Württemberg',
                'latest_date' => '2024-12-31',
                'active' => true,
                'lat' => 49.0069,
                'lon' => 8.4037
            ],
            [
                'id' => '01270',
                'name' => 'Köln-Bonn',
                'location' => 'Köln, Deutschland',
                'elevation' => 91,
                'start_year' => 1990,
                'measurement_count' => 12450,
                'state' => 'NRW',
                'latest_date' => '2024-12-31',
                'active' => true,
                'lat' => 50.9375,
                'lon' => 6.9603
            ],
            [
                'id' => '01161',
                'name' => 'Leipzig',
                'location' => 'Leipzig, Deutschland',
                'elevation' => 132,
                'start_year' => 1990,
                'measurement_count' => 12450,
                'state' => 'Sachsen',
                'latest_date' => '2024-12-31',
                'active' => true,
                'lat' => 51.3397,
                'lon' => 12.3731
            ],
            [
                'id' => '01050',
                'name' => 'München-Stadt',
                'location' => 'München, Deutschland',
                'elevation' => 448,
                'start_year' => 1990,
                'measurement_count' => 12450,
                'state' => 'Bayern',
                'latest_date' => '2024-12-31',
                'active' => true,
                'lat' => 48.1351,
                'lon' => 11.5820
            ],
            [
                'id' => '01207',
                'name' => 'Nürnberg',
                'location' => 'Nürnberg, Deutschland',
                'elevation' => 312,
                'start_year' => 1990,
                'measurement_count' => 12450,
                'state' => 'Bayern',
                'latest_date' => '2024-12-31',
                'active' => true,
                'lat' => 49.4521,
                'lon' => 11.0767
            ],
            [
                'id' => '01346',
                'name' => 'Rostock-Warnemünde',
                'location' => 'Rostock, Deutschland',
                'elevation' => 4,
                'start_year' => 1990,
                'measurement_count' => 12450,
                'state' => 'Mecklenburg-Vorpommern',
                'latest_date' => '2024-12-31',
                'active' => true,
                'lat' => 54.1814,
                'lon' => 12.0933
            ],
            [
                'id' => '01303',
                'name' => 'Saarbrücken-Ensheim',
                'location' => 'Saarbrücken, Deutschland',
                'elevation' => 322,
                'start_year' => 1990,
                'measurement_count' => 12450,
                'state' => 'Saarland',
                'latest_date' => '2024-12-31',
                'active' => true,
                'lat' => 49.2144,
                'lon' => 7.1095
            ],
            [
                'id' => '01297',
                'name' => 'Stuttgart-Echterdingen',
                'location' => 'Stuttgart, Deutschland',
                'elevation' => 371,
                'start_year' => 1990,
                'measurement_count' => 12450,
                'state' => 'Baden-Württemberg',
                'latest_date' => '2024-12-31',
                'active' => true,
                'lat' => 48.6895,
                'lon' => 9.2220
            ]
        ];

        // Apply filters if provided
        $filteredStations = $stations;
        
        if ($request->has('state')) {
            $filteredStations = array_filter($filteredStations, function($station) use ($request) {
                return $station['state'] === $request->input('state');
            });
        }
        
        if ($request->has('active')) {
            $active = filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN);
            $filteredStations = array_filter($filteredStations, function($station) use ($active) {
                return $station['active'] === $active;
            });
        }

        return response()->json([
            'success' => true,
            'data' => array_values($filteredStations),
            'meta' => [
                'total' => count($filteredStations),
                'filtered' => count($filteredStations),
                'page' => 1,
                'per_page' => 100
            ]
        ]);
    }

    /**
     * Get a specific station by ID
     */
    public function show(string $id): JsonResponse
    {
        // For now, return mock data
        $stations = $this->getStationsData();
        $station = collect($stations)->firstWhere('id', $id);

        if (!$station) {
            return response()->json([
                'success' => false,
                'message' => 'Station not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $station
        ]);
    }

    /**
     * Search stations by name
     */
    public function search(string $query): JsonResponse
    {
        $stations = $this->getStationsData();
        
        $filteredStations = array_filter($stations, function($station) use ($query) {
            return stripos($station['name'], $query) !== false || 
                   stripos($station['location'], $query) !== false ||
                   stripos($station['state'], $query) !== false;
        });

        return response()->json([
            'success' => true,
            'data' => array_values($filteredStations),
            'meta' => [
                'query' => $query,
                'total' => count($filteredStations)
            ]
        ]);
    }

    /**
     * Get stations data (helper method)
     */
    private function getStationsData(): array
    {
        return [
            [
                'id' => '01048',
                'name' => 'Berlin-Tempelhof',
                'location' => 'Berlin, Deutschland',
                'elevation' => 48,
                'start_year' => 1990,
                'measurement_count' => 12450,
                'state' => 'Berlin',
                'latest_date' => '2024-12-31',
                'active' => true,
                'lat' => 52.4828,
                'lon' => 13.3893
            ],
            // ... (same data as in index method)
        ];
    }
}