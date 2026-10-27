<?php

use App\Http\Controllers\MonitoredEndpointController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MonitoredEndpointController::class, 'index'])
    ->name('endpoints.index');

Route::post('/endpoints', [MonitoredEndpointController::class, 'store'])
    ->name('endpoints.store');

Route::delete('/endpoints/{endpoint}', [MonitoredEndpointController::class, 'destroy'])
    ->name('endpoints.destroy');
