<?php

use App\Http\Controllers\Media\MovieController;
use App\Http\Controllers\Media\MovieStatsController;
use App\Http\Controllers\Media\TvSeriesController;
use App\Http\Controllers\Media\TvStatsController;
use Illuminate\Support\Facades\Route;

/**
 * Movie Routes
 */
Route::prefix('movies')->group(function () {
    Route::get('/', [MovieController::class, 'index'])->name('movies.index');
    Route::get('/stats', [MovieStatsController::class, 'index'])->name('movies.stats');
    Route::get('/{movie}', [MovieController::class, 'show'])->name('movies.show');
    Route::post('/search', [MovieController::class, 'search'])->name('movies.search');
    Route::post('/store', [MovieController::class, 'store'])->name('movies.store');
    Route::post('/{movie}/watch', [MovieController::class, 'markWatched'])->name('movies.watch');
    Route::delete('/watches/{watch}', [MovieController::class, 'deleteWatch'])->name('movies.watches.destroy');
    Route::delete('/{movie}', [MovieController::class, 'destroy'])->name('movies.destroy');
});

/**
 * TV Series Routes
 */
Route::prefix('tv')->group(function () {
    Route::get('/', [TvSeriesController::class, 'index'])->name('tv.index');
    Route::get('/stats', [TvStatsController::class, 'index'])->name('tv.stats');
    Route::get('/{series}', [TvSeriesController::class, 'show'])->name('tv.show');
    Route::post('/search', [TvSeriesController::class, 'search'])->name('tv.search');
    Route::post('/store', [TvSeriesController::class, 'store'])->name('tv.store');
    Route::post('/episodes/{episode}/watch', [TvSeriesController::class, 'addEpisodeWatch'])->name('tv.episodes.watch');
    Route::post('/{series}/bulk-watch', [TvSeriesController::class, 'bulkWatch'])->name('tv.bulk-watch');
    Route::post('/{series}/refresh', [TvSeriesController::class, 'refresh'])->name('tv.refresh');
    Route::delete('/watches/{watch}', [TvSeriesController::class, 'deleteWatch'])->name('tv.watches.destroy');
    Route::delete('/{series}', [TvSeriesController::class, 'destroy'])->name('tv.destroy');
});
