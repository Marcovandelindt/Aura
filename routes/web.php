<?php

use App\Http\Controllers\Spotify\SpotifyAuthController;
use Illuminate\Support\Facades\Route;

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

/**
 * Mood Routes
 */
Route::prefix('moods')->group(function () {
    Route::get('/', [App\Http\Controllers\MoodController::class, 'index'])->name('moods.index');
    Route::get('/track', [App\Http\Controllers\MoodController::class, 'getTrackMoods'])->name('moods.track');
    Route::post('/add', [App\Http\Controllers\MoodController::class, 'addMoodToTrack'])->name('moods.add');
    Route::delete('/remove', [App\Http\Controllers\MoodController::class, 'removeMoodFromTrack'])->name('moods.remove');
    Route::post('/create', [App\Http\Controllers\MoodController::class, 'store'])->name('moods.store');
});

/**
 * Settings Routes
 */
Route::prefix('settings')->group(function () {
    Route::get('/moods', [App\Http\Controllers\MoodSettingsController::class, 'index'])->name('settings.moods');
    Route::post('/moods', [App\Http\Controllers\MoodSettingsController::class, 'store'])->name('settings.moods.store');
    Route::put('/moods/{mood}', [App\Http\Controllers\MoodSettingsController::class, 'update'])->name('settings.moods.update');
    Route::delete('/moods/{mood}', [App\Http\Controllers\MoodSettingsController::class, 'destroy'])->name('settings.moods.destroy');
    Route::post('/moods/{mood}/toggle', [App\Http\Controllers\MoodSettingsController::class, 'toggle'])->name('settings.moods.toggle');
});

/**
 * PlayStation Routes
 */
Route::prefix('playstation')->group(function () {
    Route::get('/', [App\Http\Controllers\PlayStationController::class, 'index'])->name('playstation.index');
    Route::get('/sessions', [App\Http\Controllers\PlayStationController::class, 'sessions'])->name('playstation.sessions');
    Route::get('/stats', [App\Http\Controllers\PlayStationStatsController::class, 'index'])->name('playstation.stats');
    Route::get('/games/{game}', [App\Http\Controllers\PlayStationController::class, 'show'])->name('playstation.games.show');
    Route::post('/sync', [App\Http\Controllers\PlayStationController::class, 'sync'])->name('playstation.sync');
});
