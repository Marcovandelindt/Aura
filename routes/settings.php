<?php

use App\Http\Controllers\Settings\GeneralSettingsController;
use App\Http\Controllers\Settings\MoodSettingsController;
use App\Http\Controllers\Settings\ThemeController;
use Illuminate\Support\Facades\Route;

Route::prefix('theme')->group(function () {
    Route::post('/switch', [ThemeController::class, 'switch'])->name('theme.switch');
    Route::get('/current', [ThemeController::class, 'current'])->name('theme.current');
});

Route::prefix('settings')->group(function () {
    Route::get('/', [GeneralSettingsController::class, 'index'])->name('settings.general');
    Route::put('/', [GeneralSettingsController::class, 'update'])->name('settings.general.update');
    Route::get('/moods', [MoodSettingsController::class, 'index'])->name('settings.moods');
    Route::post('/moods', [MoodSettingsController::class, 'store'])->name('settings.moods.store');
    Route::put('/moods/{mood}', [MoodSettingsController::class, 'update'])->name('settings.moods.update');
    Route::delete('/moods/{mood}', [MoodSettingsController::class, 'destroy'])->name('settings.moods.destroy');
    Route::post('/moods/{mood}/toggle', [MoodSettingsController::class, 'toggle'])->name('settings.moods.toggle');
});
