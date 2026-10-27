<?php

use App\Http\Controllers\MonitoredEndpointController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MonitoredEndpointController::class, 'index'])
    ->name('endpoints.index');

Route::post('/endpoints', [MonitoredEndpointController::class, 'store'])
    ->name('endpoints.store');

Route::delete('/endpoints/{endpoint}', [MonitoredEndpointController::class, 'destroy'])
    ->name('endpoints.destroy');
Route::post(
    '/endpoints/{endpoint}/check',
    [MonitoredEndpointController::class, 'check']
)->name('endpoints.check');
Route::get(
    '/endpoints/{endpoint}',
    [MonitoredEndpointController::class, 'show']
)->name('endpoints.show');