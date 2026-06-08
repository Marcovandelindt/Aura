<?php

use App\Http\Controllers\Gaming\BacklogController;
use App\Http\Controllers\Gaming\GameController;
use App\Http\Controllers\Gaming\GenreController;
use App\Http\Controllers\Gaming\NintendoSwitchController;
use App\Http\Controllers\Gaming\NintendoSwitchSessionController;
use App\Http\Controllers\Gaming\NintendoSwitchStatsController;
use App\Http\Controllers\Gaming\PlayStationController;
use App\Http\Controllers\Gaming\PlayStationStatsController;
use App\Http\Controllers\Gaming\RecommendationController;
use App\Http\Controllers\Gaming\SteamController;
use Illuminate\Support\Facades\Route;

/**
 * Game Routes
 */
Route::prefix('games')->middleware(['spotify.connected'])->group(function () {
    Route::get('/', [GameController::class, 'index'])->name('games.index');
    Route::get('/{game:slug}', [GameController::class, 'show'])->name('games.show');
});

/**
 * PlayStation Routes
 */
Route::prefix('playstation')->group(function () {
    Route::get('/', [PlayStationController::class, 'index'])->name('playstation.index');
    Route::get('/create', [PlayStationController::class, 'create'])->name('playstation.create');
    Route::post('/', [PlayStationController::class, 'store'])->name('playstation.store');
    Route::get('/sessions', [PlayStationController::class, 'sessions'])->name('playstation.sessions');
    Route::get('/stats', [PlayStationStatsController::class, 'index'])->name('playstation.stats');
    Route::get('/games/{game}', [PlayStationController::class, 'show'])->name('playstation.games.show');
    Route::get('/games/{game}/edit', [PlayStationController::class, 'edit'])->name('playstation.games.edit');
    Route::put('/games/{game}', [PlayStationController::class, 'update'])->name('playstation.games.update');
    Route::post('/games/{game}/convert-to-manual', [PlayStationController::class, 'convertToManual'])->name('playstation.games.convert-to-manual');
    Route::post('/sync', [PlayStationController::class, 'sync'])->name('playstation.sync');
    Route::get('/wat-deed-ik', [PlayStationController::class, 'watDeedIk'])->name('playstation.wat-deed-ik');
});

/**
 * Nintendo Switch Routes
 */
Route::prefix('nintendo-switch')->group(function () {
    Route::get('/', [NintendoSwitchController::class, 'index'])->name('nintendo-switch.index');
    Route::get('/create', [NintendoSwitchController::class, 'create'])->name('nintendo-switch.create');
    Route::post('/', [NintendoSwitchController::class, 'store'])->name('nintendo-switch.store');
    Route::get('/sessions', [NintendoSwitchController::class, 'sessions'])->name('nintendo-switch.sessions');
    Route::get('/stats', [NintendoSwitchStatsController::class, 'index'])->name('nintendo-switch.stats');
    Route::get('/games/{game}', [NintendoSwitchController::class, 'show'])->name('nintendo-switch.games.show');
    Route::get('/games/{game}/edit', [NintendoSwitchController::class, 'edit'])->name('nintendo-switch.games.edit');
    Route::put('/games/{game}', [NintendoSwitchController::class, 'update'])->name('nintendo-switch.games.update');
    Route::delete('/games/{game}', [NintendoSwitchController::class, 'destroy'])->name('nintendo-switch.games.destroy');
    Route::post('/games/{game}/sessions', [NintendoSwitchSessionController::class, 'store'])->name('nintendo-switch.sessions.store');
    Route::put('/sessions/{session}', [NintendoSwitchSessionController::class, 'update'])->name('nintendo-switch.sessions.update');
    Route::delete('/sessions/{session}', [NintendoSwitchSessionController::class, 'destroy'])->name('nintendo-switch.sessions.destroy');
});

/**
 * Steam Routes
 */
Route::prefix('steam')->group(function () {
    Route::get('/', [SteamController::class, 'index'])->name('steam.index');
    Route::get('/settings', [SteamController::class, 'settings'])->name('steam.settings');
    Route::get('/games/{game}', [SteamController::class, 'show'])->name('steam.games.show');
    Route::get('/games/{game}/edit', [SteamController::class, 'edit'])->name('steam.games.edit');
    Route::put('/games/{game}', [SteamController::class, 'update'])->name('steam.games.update');
    Route::post('/sync', [SteamController::class, 'sync'])->name('steam.sync');
    Route::post('/test-connection', [SteamController::class, 'testConnection'])->name('steam.test-connection');
});

/**
 * Backlog Routes
 */
Route::prefix('backlog')->group(function () {
    Route::get('/', [BacklogController::class, 'index'])->name('backlog.index');
    Route::patch('/{type}/{id}/status', [BacklogController::class, 'updateStatus'])->name('backlog.update-status');
});

/**
 * Genre Routes
 */
Route::prefix('genres')->group(function () {
    Route::post('/', [GenreController::class, 'store'])->name('genres.store');
});

/**
 * Recommendation Routes
 */
Route::prefix('recommend')->group(function () {
    Route::get('/{platform}', [RecommendationController::class, 'recommend'])->name('recommend.get');
    Route::post('/{platform}/{id}/accept', [RecommendationController::class, 'accept'])->name('recommend.accept');
});
