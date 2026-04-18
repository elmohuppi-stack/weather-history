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
    Route::get('/measurements/station/{stationId}', [\App\Http\Controllers\Api\MeasurementController::class, 'byStation']);
    Route::get('/measurements/date-range', [\App\Http\Controllers\Api\MeasurementController::class, 'byDateRange']);
    
    // Statistics endpoints
    Route::get('/statistics/stations', [\App\Http\Controllers\Api\StatisticsController::class, 'stations']);
    Route::get('/statistics/measurements', [\App\Http\Controllers\Api\StatisticsController::class, 'measurements']);
    Route::get('/statistics/parameters', [\App\Http\Controllers\Api\StatisticsController::class, 'parameters']);
    
    // Map data
    Route::get('/maps/stations', [\App\Http\Controllers\Api\MapController::class, 'stations']);
    
    // Export endpoints
    Route::post('/exports', [\App\Http\Controllers\Api\ExportController::class, 'create']);
});