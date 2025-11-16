<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Spotify\SpotifyAuthController;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home.index');

/**
 * Spotify Routes
 */
Route::get('/spotify/auth', [SpotifyAuthController::class, 'redirect'])->name('spotify.auth');
Route::get('/callback/spotify', [SpotifyAuthController::class, 'callback'])->name('spotify.callback');


/**
 * Music Routes
 */
Route::middleware(['spotify.connected'])->group(function () {
    Route::get('/music', [App\Http\Controllers\MusicController::class, 'index'])->name('music.index');
    Route::get('/music/top', [App\Http\Controllers\MusicController::class, 'topTracks'])->name('music.top');
    Route::post('/music/sync', [App\Http\Controllers\MusicController::class, 'syncTracks'])->name('music.sync');
});