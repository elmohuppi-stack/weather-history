<?php

namespace App\Http\Controllers;

use App\Models\ImportLog;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ImportController extends Controller
{
    /**
     * Display a listing of import logs.
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'type' => 'string|in:historical,recent,full,station_add,update',
            'station_id' => 'string|exists:stations,id',
            'success' => 'boolean',
            'user_initiated' => 'boolean',
            'date_from' => 'date',
            'date_to' => 'date',
            'sort_by' => 'string|in:created_at,records_imported,duration_seconds',
            'sort_order' => 'string|in:asc,desc',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        
        $query = ImportLog::with('station')
            ->select('import_logs.*')
            ->when(isset($validated['type']), function ($q) use ($validated) {
                return $q->where('import_type', $validated['type']);
            })
            ->when(isset($validated['station_id']), function ($q) use ($validated) {
                return $q->where('station_id', $validated['station_id']);
            })
            ->when(isset($validated['success']), function ($q) use ($validated) {
                return $q->where('success', $validated['success']);
            })
            ->when(isset($validated['user_initiated']), function ($q) use ($validated) {
                return $q->where('user_initiated', $validated['user_initiated']);
            })
            ->when(isset($validated['date_from']), function ($q) use ($validated) {
                return $q->where('created_at', '>=', $validated['date_from']);
            })
            ->when(isset($validated['date_to']), function ($q) use ($validated) {
                return $q->where('created_at', '<=', $validated['date_to'] . ' 23:59:59');
            });

        // Apply sorting
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $validated['per_page'] ?? 20;
        $imports = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $imports->items(),
            'meta' => [
                'current_page' => $imports->currentPage(),
                'last_page' => $imports->lastPage(),
                'per_page' => $imports->perPage(),
                'total' => $imports->total(),
                'from' => $imports->firstItem(),
                'to' => $imports->lastItem(),
            ],
        ]);
    }

    /**
     * Get import statistics.
     */
    public function statistics(): JsonResponse
    {
        $stats = DB::table('import_logs')
            ->select(
                DB::raw('COUNT(*) as total_imports'),
                DB::raw('SUM(CASE WHEN success = true THEN 1 ELSE 0 END) as successful_imports'),
                DB::raw('SUM(CASE WHEN success = false THEN 1 ELSE 0 END) as failed_imports'),
                DB::raw('SUM(records_processed) as total_records_processed'),
                DB::raw('SUM(records_imported) as total_records_imported'),
                DB::raw('SUM(records_skipped) as total_records_skipped'),
                DB::raw('SUM(records_failed) as total_records_failed'),
                DB::raw('AVG(duration_seconds) as avg_duration_seconds'),
                DB::raw('MAX(created_at) as last_import_date')
            )
            ->first();

        $importsByType = DB::table('import_logs')
            ->select('import_type', DB::raw('COUNT(*) as count'))
            ->groupBy('import_type')
            ->get()
            ->pluck('count', 'import_type');

        $importsByStation = DB::table('import_logs')
            ->join('stations', 'import_logs.station_id', '=', 'stations.id')
            ->select('stations.id', 'stations.name', DB::raw('COUNT(*) as import_count'))
            ->whereNotNull('station_id')
            ->groupBy('stations.id', 'stations.name')
            ->orderBy('import_count', 'desc')
            ->limit(10)
            ->get();

        $recentImports = ImportLog::with('station')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'overall' => $stats,
                'by_type' => $importsByType,
                'by_station' => $importsByStation,
                'recent_imports' => $recentImports,
            ],
        ]);
    }

    /**
     * Display the specified import log.
     */
    public function show(string $id): JsonResponse
    {
        $importLog = ImportLog::with('station')->find($id);

        if (!$importLog) {
            return response()->json([
                'success' => false,
                'message' => 'Import log not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $importLog,
        ]);
    }

    /**
     * Get import logs for a specific station.
     */
    public function stationImports(string $stationId): JsonResponse
    {
        $station = Station::find($stationId);

        if (!$station) {
            return response()->json([
                'success' => false,
                'message' => 'Station not found',
            ], 404);
        }

        $imports = ImportLog::where('station_id', $stationId)
            ->with('station')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'station' => $station,
                'imports' => $imports->items(),
                'meta' => [
                    'current_page' => $imports->currentPage(),
                    'last_page' => $imports->lastPage(),
                    'per_page' => $imports->perPage(),
                    'total' => $imports->total(),
                ],
            ],
        ]);
    }

    /**
     * Trigger a manual import.
     */
    public function triggerImport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:historical,recent,full,station_add,update',
            'station_id' => 'nullable|string|exists:stations,id',
            'parameters' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        // Create a pending import log
        $importLog = ImportLog::create([
            'import_type' => $validated['type'],
            'station_id' => $validated['station_id'] ?? null,
            'operation' => ImportLog::OPERATION_IMPORT,
            'success' => false,
            'parameters' => $validated['parameters'] ?? [],
            'user_initiated' => true,
        ]);

        // In a real implementation, this would dispatch a job to run the import
        // For now, we'll return a response indicating the import was triggered
        return response()->json([
            'success' => true,
            'message' => 'Import triggered successfully',
            'data' => [
                'import_id' => $importLog->id,
                'type' => $validated['type'],
                'station_id' => $validated['station_id'],
                'status' => 'pending',
                'estimated_duration' => 'Varies based on import type',
            ],
        ]);
    }

    /**
     * Get import status by ID.
     */
    public function importStatus(string $id): JsonResponse
    {
        $importLog = ImportLog::with('station')->find($id);

        if (!$importLog) {
            return response()->json([
                'success' => false,
                'message' => 'Import log not found',
            ], 404);
        }

        $status = $importLog->success ? 'completed' : ($importLog->error_message ? 'failed' : 'pending');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $importLog->id,
                'type' => $importLog->import_type,
                'station' => $importLog->station,
                'operation' => $importLog->operation,
                'status' => $status,
                'success' => $importLog->success,
                'records_processed' => $importLog->records_processed,
                'records_imported' => $importLog->records_imported,
                'records_skipped' => $importLog->records_skipped,
                'records_failed' => $importLog->records_failed,
                'duration_seconds' => $importLog->duration_seconds,
                'formatted_duration' => $importLog->formatted_duration,
                'error_message' => $importLog->error_message,
                'user_initiated' => $importLog->user_initiated,
                'created_at' => $importLog->created_at,
                'updated_at' => $importLog->updated_at,
            ],
        ]);
    }

    /**
     * Delete an import log.
     */
    public function destroy(string $id): JsonResponse
    {
        $importLog = ImportLog::find($id);

        if (!$importLog) {
            return response()->json([
                'success' => false,
                'message' => 'Import log not found',
            ], 404);
        }

        $importLog->delete();

        return response()->json([
            'success' => true,
            'message' => 'Import log deleted successfully',
        ]);
    }

    /**
     * Clear old import logs (keep only last 1000 records).
     */
    public function clearOldLogs(): JsonResponse
    {
        $totalLogs = ImportLog::count();
        
        if ($totalLogs <= 1000) {
            return response()->json([
                'success' => true,
                'message' => 'No old logs to clear (total logs: ' . $totalLogs . ')',
            ]);
        }

        $logsToDelete = $totalLogs - 1000;
        
        // Get IDs of oldest logs to delete
        $oldLogIds = ImportLog::orderBy('created_at', 'asc')
            ->limit($logsToDelete)
            ->pluck('id')
            ->toArray();

        $deleted = ImportLog::whereIn('id', $oldLogIds)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cleared ' . $deleted . ' old import logs',
            'data' => [
                'deleted_count' => $deleted,
                'remaining_count' => $totalLogs - $deleted,
            ],
        ]);
    }
}
