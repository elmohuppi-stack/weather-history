<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MapController extends Controller
{
    /**
     * Get GeoJSON data for all stations.
     */
    public function stations(): JsonResponse
    {
        $stations = Station::all();
        
        $features = [];
        
        foreach ($stations as $station) {
            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [
                        (float) $station->lon,
                        (float) $station->lat,
                    ]
                ],
                'properties' => [
                    'id' => $station->id,
                    'name' => $station->name,
                    'location' => $station->location,
                    'elevation' => $station->elevation,
                    'state' => $station->state,
                    'start_year' => $station->start_year,
                    'measurement_count' => $station->measurement_count,
                    'latest_date' => $station->latest_date,
                    'active' => $station->active,
                ]
            ];
        }
        
        $geojson = [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
        
        return response()->json([
            'success' => true,
            'data' => $geojson,
            'meta' => [
                'total_stations' => count($features),
                'updated_at' => now()->toIso8601String(),
            ]
        ]);
    }

    /**
     * Get stations within a bounding box.
     */
    public function withinBounds(Request $request): JsonResponse
    {
        $request->validate([
            'north' => 'required|numeric|between:-90,90',
            'south' => 'required|numeric|between:-90,90',
            'east' => 'required|numeric|between:-180,180',
            'west' => 'required|numeric|between:-180,180',
        ]);
        
        $north = $request->north;
        $south = $request->south;
        $east = $request->east;
        $west = $request->west;
        
        // For now, return all stations (mock implementation)
        // In production, this would query stations within the bounding box
        
        $stations = Station::all();
        
        $features = [];
        
        foreach ($stations as $station) {
            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [
                        (float) $station->lon,
                        (float) $station->lat,
                    ]
                ],
                'properties' => [
                    'id' => $station->id,
                    'name' => $station->name,
                    'location' => $station->location,
                    'elevation' => $station->elevation,
                    'state' => $station->state,
                ]
            ];
        }
        
        $geojson = [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
        
        return response()->json([
            'success' => true,
            'data' => $geojson,
            'meta' => [
                'bounds' => [
                    'north' => $north,
                    'south' => $south,
                    'east' => $east,
                    'west' => $west,
                ],
                'total_stations' => count($features),
                'updated_at' => now()->toIso8601String(),
            ]
        ]);
    }

    /**
     * Get heatmap data for a specific parameter.
     */
    public function heatmap(Request $request): JsonResponse
    {
        $request->validate([
            'parameter' => 'required|string|in:temperature,precipitation,sunshine',
            'year' => 'nullable|integer|min:1990|max:2024',
            'month' => 'nullable|integer|min:1|max:12',
        ]);
        
        $parameter = $request->get('parameter', 'temperature');
        $year = $request->get('year', 2024);
        $month = $request->get('month');
        
        // For now, return mock heatmap data
        // In production, this would query aggregated measurement data
        
        $heatmapData = [
            'parameter' => $parameter,
            'year' => $year,
            'month' => $month,
            'stations' => [
                [
                    'station_id' => '01048',
                    'station_name' => 'Berlin-Tempelhof',
                    'lat' => 52.4828,
                    'lon' => 13.3893,
                    'value' => 5.2,
                    'unit' => $parameter === 'temperature' ? '°C' : ($parameter === 'precipitation' ? 'mm' : 'hours'),
                ],
                [
                    'station_id' => '01001',
                    'station_name' => 'Bremen',
                    'lat' => 53.0793,
                    'lon' => 8.8017,
                    'value' => 6.1,
                    'unit' => $parameter === 'temperature' ? '°C' : ($parameter === 'precipitation' ? 'mm' : 'hours'),
                ],
                [
                    'station_id' => '01072',
                    'station_name' => 'Dresden-Klotzsche',
                    'lat' => 51.1278,
                    'lon' => 13.7542,
                    'value' => 4.8,
                    'unit' => $parameter === 'temperature' ? '°C' : ($parameter === 'precipitation' ? 'mm' : 'hours'),
                ],
            ],
            'metadata' => [
                'interpolation' => 'inverse_distance_weighting',
                'grid_size' => '0.1°',
                'color_scale' => 'viridis',
            ]
        ];
        
        return response()->json([
            'success' => true,
            'data' => $heatmapData,
            'meta' => [
                'parameter' => $parameter,
                'period' => $month ? "{$year}-{$month}" : $year,
                'station_count' => count($heatmapData['stations']),
                'updated_at' => now()->toIso8601String(),
            ]
        ]);
    }

    /**
     * Get cluster data for map visualization.
     */
    public function clusters(Request $request): JsonResponse
    {
        $request->validate([
            'zoom' => 'required|integer|min:1|max:18',
        ]);
        
        $zoom = $request->get('zoom', 10);
        
        // For now, return mock cluster data
        // In production, this would calculate clusters based on zoom level
        
        $clusters = [
            'zoom' => $zoom,
            'clusters' => [
                [
                    'lat' => 52.5,
                    'lon' => 13.4,
                    'count' => 3,
                    'stations' => ['01048', '01001', '01072'],
                    'bounds' => [
                        'north' => 53.0,
                        'south' => 52.0,
                        'east' => 14.0,
                        'west' => 13.0,
                    ]
                ],
                [
                    'lat' => 48.1,
                    'lon' => 11.6,
                    'count' => 2,
                    'stations' => ['01050', '01207'],
                    'bounds' => [
                        'north' => 48.5,
                        'south' => 47.7,
                        'east' => 12.0,
                        'west' => 11.2,
                    ]
                ],
            ],
            'metadata' => [
                'algorithm' => 'grid-based',
                'grid_size' => $zoom < 8 ? 'large' : ($zoom < 12 ? 'medium' : 'small'),
                'total_stations' => 16,
            ]
        ];
        
        return response()->json([
            'success' => true,
            'data' => $clusters,
            'meta' => [
                'zoom' => $zoom,
                'cluster_count' => count($clusters['clusters']),
                'updated_at' => now()->toIso8601String(),
            ]
        ]);
    }
}
