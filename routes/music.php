<?php

use App\Http\Controllers\Music\AlbumController;
use App\Http\Controllers\Music\ArtistController;
use App\Http\Controllers\Music\LastfmController;
use App\Http\Controllers\Music\LastfmTrackController;
use App\Http\Controllers\Music\MoodController;
use App\Http\Controllers\Music\MusicAllTimeController;
use App\Http\Controllers\Music\MusicController;
use App\Http\Controllers\Music\MusicReportController;
use App\Http\Controllers\Music\MusicStatsController;
use App\Http\Controllers\Music\TrackController;
use Illuminate\Support\Facades\Route;

/**
 * Music Routes
 */
Route::middleware(['spotify.connected'])->group(function () {
    Route::get('/music', [MusicController::class, 'index'])->name('music.index');
    Route::get('/music/stats', [MusicStatsController::class, 'index'])->name('music.stats');
    Route::get('/music/top', [MusicController::class, 'topTracks'])->name('music.top');
    Route::get('/music/all-time', [MusicAllTimeController::class, 'index'])->name('music.all-time');
    Route::get('/music/report/year/{year?}', [MusicReportController::class, 'year'])->name('music.report.year');
    Route::post('/music/sync', [MusicController::class, 'syncTracks'])->name('music.sync');
    Route::get('/tracks/lastfm/{artist}/{track}', [LastfmTrackController::class, 'show'])->name('tracks.lastfm');
    Route::get('/tracks/{track}', [TrackController::class, 'show'])->name('tracks.show');
    Route::get('/artists/{artist}', [ArtistController::class, 'show'])->name('artists.show');
    Route::get('/albums/{album}', [AlbumController::class, 'show'])->name('albums.show');
});

/**
 * Last.fm Routes
 */
Route::prefix('lastfm')->group(function () {
    Route::get('/', [LastfmController::class, 'index'])->name('lastfm.index');
    Route::post('/import', [LastfmController::class, 'startImport'])->name('lastfm.import');
    Route::get('/import/status', [LastfmController::class, 'importStatus'])->name('lastfm.import.status');
    Route::post('/clear', [LastfmController::class, 'clearImport'])->name('lastfm.clear');
    Route::post('/enrich', [LastfmController::class, 'startEnrichment'])->name('lastfm.enrich');
    Route::get('/enrich/status', [LastfmController::class, 'enrichmentStatus'])->name('lastfm.enrich.status');
    Route::post('/enrich-track', [LastfmController::class, 'enrichTrack'])->name('lastfm.enrich-track');
    Route::post('/set-duration', [LastfmController::class, 'setDuration'])->name('lastfm.set-duration');
    Route::get('/missing-duration', [LastfmController::class, 'missingDuration'])->name('lastfm.missing-duration');
    Route::post('/deduplicate', [LastfmController::class, 'deduplicateAll'])->name('lastfm.deduplicate');
    Route::get('/corrections', [LastfmController::class, 'corrections'])->name('lastfm.corrections');
    Route::post('/corrections', [LastfmController::class, 'saveCorrection'])->name('lastfm.corrections.save');
    Route::delete('/corrections/{id}', [LastfmController::class, 'deleteCorrection'])->name('lastfm.corrections.delete');
    Route::get('/corrections/search', [LastfmController::class, 'searchTracksForCorrection'])->name('lastfm.corrections.search');
});

/**
 * Mood Routes
 */
Route::prefix('moods')->group(function () {
    Route::get('/', [MoodController::class, 'index'])->name('moods.index');
    Route::get('/track', [MoodController::class, 'getTrackMoods'])->name('moods.track');
    Route::post('/add', [MoodController::class, 'addMoodToTrack'])->name('moods.add');
    Route::delete('/remove', [MoodController::class, 'removeMoodFromTrack'])->name('moods.remove');
    Route::post('/create', [MoodController::class, 'store'])->name('moods.store');
    Route::get('/track-games', [MoodController::class, 'getTrackGames'])->name('moods.track-games');
    Route::post('/assign-game', [MoodController::class, 'assignGameToTrack'])->name('moods.assign-game');
});
