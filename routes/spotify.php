<?php

use App\Http\Controllers\Spotify\SpotifyAuthController;
use App\Http\Controllers\Spotify\SpotifyPlaybackController;
use Illuminate\Support\Facades\Route;

Route::get('/spotify/auth', [SpotifyAuthController::class, 'redirect'])->name('spotify.auth');
Route::get('/callback/spotify', [SpotifyAuthController::class, 'callback'])->name('spotify.callback');

Route::middleware(['spotify.connected'])->prefix('spotify')->group(function () {
    Route::post('/playback/toggle', [SpotifyPlaybackController::class, 'togglePlayPause'])->name('spotify.playback.toggle');
    Route::post('/playback/next', [SpotifyPlaybackController::class, 'skipToNext'])->name('spotify.playback.next');
    Route::post('/playback/previous', [SpotifyPlaybackController::class, 'skipToPrevious'])->name('spotify.playback.previous');
});
