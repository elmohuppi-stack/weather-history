<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Station;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StationController extends Controller
{
    /**
     * Get all stations.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Station::query()->withCount('measurements')->withMax('measurements', 'date');

        if ($request->filled('state')) {
            $query->where('state', $request->input('state'));
        }

        if ($request->has('active')) {
            $active = filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($active !== null) {
                $query->where('active', $active);
            }
        }

        $perPage = min(max((int) $request->get('per_page', 100), 1), 500);
        $page = max((int) $request->get('page', 1), 1);

        $stations = $query
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => collect($stations->items())->map(fn(Station $station) => $this->transformStation($station))->values(),
            'meta' => [
                'total' => $stations->total(),
                'filtered' => $stations->count(),
                'page' => $stations->currentPage(),
                'per_page' => $stations->perPage(),
            ],
        ]);
    }

    /**
     * Get a specific station by ID.
     */
    public function show(string $id): JsonResponse
    {
        $station = Station::query()
            ->withCount('measurements')
            ->withMax('measurements', 'date')
            ->find($id);

        if (!$station) {
            return response()->json([
                'success' => false,
                'message' => 'Station not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->transformStation($station),
        ]);
    }

    /**
     * Search stations by ID, name, location or state.
     */
    public function search(string $query): JsonResponse
    {
        $stations = Station::query()
            ->withCount('measurements')
            ->withMax('measurements', 'date')
            ->where(function ($builder) use ($query) {
                $builder->where('id', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%")
                    ->orWhere('location', 'like', "%{$query}%")
                    ->orWhere('state', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $stations->map(fn(Station $station) => $this->transformStation($station))->values(),
            'meta' => [
                'query' => $query,
                'total' => $stations->count(),
            ],
        ]);
    }

    /**
     * Normalize station data for the API.
     */
    private function transformStation(Station $station): array
    {
        $latestDate = $station->measurements_max_date ?? $station->latest_date;
        $measurementCount = $station->measurements_count ?? $station->measurement_count ?? 0;

        return [
            'id' => $station->id,
            'name' => $station->name,
            'location' => $station->location,
            'elevation' => $station->elevation,
            'start_year' => $station->start_year,
            'measurement_count' => (int) $measurementCount,
            'state' => $station->state,
            'latest_date' => $this->formatDate($latestDate),
            'active' => (bool) $station->active,
            'lat' => (float) $station->lat,
            'lon' => (float) $station->lon,
            'description' => $station->description,
            'dwd_url' => $station->dwd_url,
        ];
    }

    /**
     * Format a date value for JSON output.
     */
    private function formatDate(mixed $value): ?string
    {
        if (!$value) {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value->toDateString();
        }

        return date('Y-m-d', strtotime((string) $value));
    }
}
