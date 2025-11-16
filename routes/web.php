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
    Route::get('/music/top', [App\Http\Controllers\MusicController::class, 'topTracks'])->name('music.top');
    Route::post('/music/sync', [App\Http\Controllers\MusicController::class, 'syncTracks'])->name('music.sync');
    Route::get('/tracks/{track}', [App\Http\Controllers\TrackController::class, 'show'])->name('tracks.show');
    Route::get('/artists/{artist}', [App\Http\Controllers\ArtistController::class, 'show'])->name('artists.show');
});