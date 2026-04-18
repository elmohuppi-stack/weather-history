<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public API routes for weather data
Route::prefix('v1')->group(function () {
    // Stations endpoints
    Route::get('/stations', [\App\Http\Controllers\Api\StationController::class, 'index']);
    Route::get('/stations/{id}', [\App\Http\Controllers\Api\StationController::class, 'show']);
    Route::get('/stations/search/{query}', [\App\Http\Controllers\Api\StationController::class, 'search']);
    
    // Measurements endpoints
    Route::get('/measurements', [\App\Http\Controllers\Api\MeasurementController::class, 'index']);
    Route::get('/measurements/station/{stationId}', [\App\Http\Controllers\Api\MeasurementController::class, 'getByStation']);
    Route::get('/measurements/date-range', [\App\Http\Controllers\Api\MeasurementController::class, 'getByDateRange']);
    Route::get('/measurements/latest', [\App\Http\Controllers\Api\MeasurementController::class, 'getLatest']);
    
    // Statistics endpoints
    Route::get('/statistics/overall', [\App\Http\Controllers\Api\StatisticsController::class, 'overall']);
    Route::get('/statistics/station/{stationId}', [\App\Http\Controllers\Api\StatisticsController::class, 'station']);
    Route::get('/statistics/climate-normals', [\App\Http\Controllers\Api\StatisticsController::class, 'climateNormals']);
    Route::get('/statistics/trends', [\App\Http\Controllers\Api\StatisticsController::class, 'trends']);
    
    // Map data endpoints
    Route::get('/maps/stations', [\App\Http\Controllers\Api\MapController::class, 'stations']);
    Route::get('/maps/within-bounds', [\App\Http\Controllers\Api\MapController::class, 'withinBounds']);
    Route::get('/maps/heatmap', [\App\Http\Controllers\Api\MapController::class, 'heatmap']);
    Route::get('/maps/clusters', [\App\Http\Controllers\Api\MapController::class, 'clusters']);
    
    // Export endpoints
    Route::post('/exports', [\App\Http\Controllers\Api\ExportController::class, 'create']);
    Route::get('/exports/{exportId}/status', [\App\Http\Controllers\Api\ExportController::class, 'status']);
    Route::get('/exports/{exportId}/download', [\App\Http\Controllers\Api\ExportController::class, 'download']);
    Route::get('/exports/formats', [\App\Http\Controllers\Api\ExportController::class, 'formats']);
});
