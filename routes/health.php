<?php

use App\Http\Controllers\Health\HealthController;
use App\Http\Controllers\Health\HealthStatsController;
use Illuminate\Support\Facades\Route;

Route::prefix('health')->group(function () {
    Route::get('/', [HealthController::class, 'index'])->name('health.index');
    Route::post('/', [HealthController::class, 'store'])->name('health.store');
    Route::get('/stats', [HealthStatsController::class, 'index'])->name('health.stats');
    Route::get('/export', [HealthController::class, 'export'])->name('health.export');
    Route::put('/{entry}', [HealthController::class, 'update'])->name('health.update');
    Route::delete('/{entry}', [HealthController::class, 'destroy'])->name('health.destroy');
});
