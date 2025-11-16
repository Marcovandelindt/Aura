<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Spotify\SpotifyAuthController;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home.index');

/**
 * Spotify Routes
 */
Route::get('/spotify/auth', [SpotifyAuthController::class, 'redirect'])->name('spotify.auth');
Route::get('/callback/spotify', [SpotifyAuthController::class, 'callback'])->name('spotify.callback');

// Spotify Playback Control Routes (AJAX)
Route::middleware(['spotify.connected'])->prefix('spotify')->group(function () {
    Route::post('/playback/toggle', [App\Http\Controllers\Spotify\SpotifyPlaybackController::class, 'togglePlayPause'])->name('spotify.playback.toggle');
    Route::post('/playback/next', [App\Http\Controllers\Spotify\SpotifyPlaybackController::class, 'skipToNext'])->name('spotify.playback.next');
    Route::post('/playback/previous', [App\Http\Controllers\Spotify\SpotifyPlaybackController::class, 'skipToPrevious'])->name('spotify.playback.previous');
});


/**
 * Music Routes
 */
Route::middleware(['spotify.connected'])->group(function () {
    Route::get('/music', [App\Http\Controllers\MusicController::class, 'index'])->name('music.index');
    Route::get('/music/stats', [App\Http\Controllers\MusicStatsController::class, 'index'])->name('music.stats');
    Route::get('/music/top', [App\Http\Controllers\MusicController::class, 'topTracks'])->name('music.top');
    Route::post('/music/sync', [App\Http\Controllers\MusicController::class, 'syncTracks'])->name('music.sync');
    Route::get('/tracks/{track}', [App\Http\Controllers\TrackController::class, 'show'])->name('tracks.show');
    Route::get('/artists/{artist}', [App\Http\Controllers\ArtistController::class, 'show'])->name('artists.show');
    Route::get('/albums/{album}', [App\Http\Controllers\AlbumController::class, 'show'])->name('albums.show');
});

/**
 * Ideas Routes
 */
Route::prefix('ideas')->group(function () {
    Route::get('/', [App\Http\Controllers\IdeasController::class, 'index'])->name('ideas.index');
    Route::post('/', [App\Http\Controllers\IdeasController::class, 'store'])->name('ideas.store');
    Route::post('/{idea}/toggle', [App\Http\Controllers\IdeasController::class, 'toggle'])->name('ideas.toggle');
    Route::put('/{idea}', [App\Http\Controllers\IdeasController::class, 'update'])->name('ideas.update');
    Route::delete('/{idea}', [App\Http\Controllers\IdeasController::class, 'destroy'])->name('ideas.destroy');
});

/**
 * Theme Routes
 */
Route::prefix('theme')->group(function () {
    Route::post('/switch', [App\Http\Controllers\ThemeController::class, 'switch'])->name('theme.switch');
    Route::get('/current', [App\Http\Controllers\ThemeController::class, 'current'])->name('theme.current');
});