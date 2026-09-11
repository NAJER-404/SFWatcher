<?php

use App\Http\Controllers\Api\SpectralApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ECTO//NET — Spectral GIS REST API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('spectral')->group(function () {
    Route::get('/incidents', [SpectralApiController::class, 'incidents'])->name('api.spectral.incidents');
    Route::post('/incidents', [SpectralApiController::class, 'storeIncident'])->name('api.spectral.incidents.store');
    Route::get('/incidents/{id}', [SpectralApiController::class, 'showIncident'])->name('api.spectral.incidents.show');
    Route::put('/incidents/{id}/status', [SpectralApiController::class, 'updateIncidentStatus'])->name('api.spectral.incidents.status');

    Route::get('/wards', [SpectralApiController::class, 'wardStations'])->name('api.spectral.wards');
    Route::get('/resources', [SpectralApiController::class, 'resources'])->name('api.spectral.resources');
    Route::get('/barangays', [SpectralApiController::class, 'barangays'])->name('api.spectral.barangays');
    Route::get('/stats', [SpectralApiController::class, 'stats'])->name('api.spectral.stats');
});
