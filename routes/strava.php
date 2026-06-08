<?php

use App\Http\Controllers\Strava\StravaAuthController;
use App\Http\Controllers\Strava\StravaController;
use App\Http\Controllers\Strava\StravaStatsController;
use Illuminate\Support\Facades\Route;

Route::get('/strava/auth', [StravaAuthController::class, 'redirect'])->name('strava.auth');
Route::get('/callback/strava', [StravaAuthController::class, 'callback'])->name('strava.callback');

Route::prefix('strava')->group(function () {
    Route::get('/', [StravaController::class, 'index'])->name('strava.index');
    Route::get('/stats', [StravaStatsController::class, 'index'])->name('strava.stats');
    Route::get('/map/heatmap', [StravaController::class, 'heatmap'])->name('strava.heatmap');
    Route::get('/{activity}', [StravaController::class, 'show'])->name('strava.show');
});
