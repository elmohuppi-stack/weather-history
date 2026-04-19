<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Measurement;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MeasurementController extends Controller
{
    /**
     * Display a listing of measurements with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Measurement::query();
        
        // Apply filters
        if ($request->has('station_id')) {
            $query->where('station_id', $request->station_id);
        }
        
        if ($request->has('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }
        
        // Pagination
        $perPage = $request->get('per_page', 100);
        $page = $request->get('page', 1);
        
        $measurements = $query->orderBy('date', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'success' => true,
            'data' => $measurements->items(),
            'meta' => [
                'total' => $measurements->total(),
                'page' => $measurements->currentPage(),
                'per_page' => $measurements->perPage(),
                'last_page' => $measurements->lastPage(),
            ]
        ]);
    }

    /**
     * Get measurements for a specific station.
     */
    public function getByStation(string $stationId, Request $request): JsonResponse
    {
        // Check if station exists
        $station = Station::find($stationId);
        
        if (!$station) {
            return response()->json([
                'success' => false,
                'message' => 'Station not found'
            ], 404);
        }
        
        $query = Measurement::where('station_id', $stationId);
        
        // Apply date filters
        if ($request->has('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }
        
        // Pagination
        $perPage = $request->get('per_page', 100);
        $page = $request->get('page', 1);
        
        $measurements = $query->orderBy('date', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'success' => true,
            'data' => $measurements->items(),
            'meta' => [
                'total' => $measurements->total(),
                'page' => $measurements->currentPage(),
                'per_page' => $measurements->perPage(),
                'last_page' => $measurements->lastPage(),
                'station' => [
                    'id' => $station->id,
                    'name' => $station->name,
                    'location' => $station->location,
                ]
            ]
        ]);
    }

    /**
     * Get measurements by date range.
     */
    public function getByDateRange(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'station_ids' => 'nullable|array',
            'station_ids.*' => 'string|exists:stations,id',
        ]);
        
        $query = Measurement::query();
        
        // Apply date range
        $query->whereBetween('date', [
            $request->start_date,
            $request->end_date
        ]);
        
        // Filter by stations if provided
        if ($request->has('station_ids') && !empty($request->station_ids)) {
            $query->whereIn('station_id', $request->station_ids);
        }
        
        // Pagination
        $perPage = $request->get('per_page', 100);
        $page = $request->get('page', 1);
        
        $measurements = $query->orderBy('date', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'success' => true,
            'data' => $measurements->items(),
            'meta' => [
                'total' => $measurements->total(),
                'page' => $measurements->currentPage(),
                'per_page' => $measurements->perPage(),
                'last_page' => $measurements->lastPage(),
                'date_range' => [
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                ]
            ]
        ]);
    }

    /**
     * Get latest measurements for all stations.
     */
    public function getLatest(): JsonResponse
    {
        // Get the latest date with measurements
        $latestDate = Measurement::max('date');
        
        if (!$latestDate) {
            return response()->json([
                'success' => true,
                'data' => [],
                'meta' => [
                    'total' => 0,
                    'date' => null,
                ]
            ]);
        }
        
        // Get latest measurements for all stations
        $latestMeasurements = Measurement::with('station')
            ->where('date', $latestDate)
            ->orderBy('station_id')
            ->limit(50) // Limit to 50 stations to avoid too much data
            ->get()
            ->map(function ($measurement) {
                return [
                    'station_id' => $measurement->station_id,
                    'station_name' => $measurement->station->name ?? 'Unknown',
                    'date' => $measurement->date,
                    'temp_mean' => $measurement->temp_mean,
                    'precipitation' => $measurement->precipitation,
                    'sunshine' => $measurement->sunshine,
                    'temp_max' => $measurement->temp_max,
                    'temp_min' => $measurement->temp_min,
                    'snow_depth' => $measurement->snow_depth,
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $latestMeasurements,
            'meta' => [
                'total' => $latestMeasurements->count(),
                'date' => $latestDate,
            ]
        ]);
    }
}
