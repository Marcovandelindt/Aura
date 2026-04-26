<?php

use App\Http\Controllers\Spotify\SpotifyAuthController;
use App\Http\Controllers\Strava\StravaAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home.index');
Route::get('/weekly', [App\Http\Controllers\WeeklyController::class, 'index'])->name('weekly.index');

/**
 * Spotify Routes
 */
Route::get('/spotify/auth', [SpotifyAuthController::class, 'redirect'])->name('spotify.auth');
Route::get('/callback/spotify', [SpotifyAuthController::class, 'callback'])->name('spotify.callback');

/**
 * Strava Routes
 */
Route::get('/strava/auth', [StravaAuthController::class, 'redirect'])->name('strava.auth');
Route::get('/callback/strava', [StravaAuthController::class, 'callback'])->name('strava.callback');
Route::prefix('strava')->group(function () {
    Route::get('/', [App\Http\Controllers\Strava\StravaController::class, 'index'])->name('strava.index');
    Route::get('/stats', [App\Http\Controllers\Strava\StravaStatsController::class, 'index'])->name('strava.stats');
    Route::get('/map/heatmap', [App\Http\Controllers\Strava\StravaController::class, 'heatmap'])->name('strava.heatmap');
    Route::get('/{activity}', [App\Http\Controllers\Strava\StravaController::class, 'show'])->name('strava.show');
});

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
    Route::get('/music/all-time', [App\Http\Controllers\MusicAllTimeController::class, 'index'])->name('music.all-time');
    Route::post('/music/sync', [App\Http\Controllers\MusicController::class, 'syncTracks'])->name('music.sync');
    Route::get('/tracks/{track}', [App\Http\Controllers\TrackController::class, 'show'])->name('tracks.show');
    Route::get('/artists/{artist}', [App\Http\Controllers\ArtistController::class, 'show'])->name('artists.show');
    Route::get('/albums/{album}', [App\Http\Controllers\AlbumController::class, 'show'])->name('albums.show');
});

/**
 * Last.fm Routes
 */
Route::prefix('lastfm')->group(function () {
    Route::get('/', [App\Http\Controllers\LastfmController::class, 'index'])->name('lastfm.index');
    Route::post('/import', [App\Http\Controllers\LastfmController::class, 'startImport'])->name('lastfm.import');
    Route::get('/import/status', [App\Http\Controllers\LastfmController::class, 'importStatus'])->name('lastfm.import.status');
    Route::post('/clear', [App\Http\Controllers\LastfmController::class, 'clearImport'])->name('lastfm.clear');
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
    Route::get('/track-games', [App\Http\Controllers\MoodController::class, 'getTrackGames'])->name('moods.track-games');
    Route::post('/assign-game', [App\Http\Controllers\MoodController::class, 'assignGameToTrack'])->name('moods.assign-game');
});

/**
 * Settings Routes
 */
Route::prefix('settings')->group(function () {
    Route::get('/', [App\Http\Controllers\GeneralSettingsController::class, 'index'])->name('settings.general');
    Route::put('/', [App\Http\Controllers\GeneralSettingsController::class, 'update'])->name('settings.general.update');
    Route::get('/moods', [App\Http\Controllers\MoodSettingsController::class, 'index'])->name('settings.moods');
    Route::post('/moods', [App\Http\Controllers\MoodSettingsController::class, 'store'])->name('settings.moods.store');
    Route::put('/moods/{mood}', [App\Http\Controllers\MoodSettingsController::class, 'update'])->name('settings.moods.update');
    Route::delete('/moods/{mood}', [App\Http\Controllers\MoodSettingsController::class, 'destroy'])->name('settings.moods.destroy');
    Route::post('/moods/{mood}/toggle', [App\Http\Controllers\MoodSettingsController::class, 'toggle'])->name('settings.moods.toggle');
});

/**
 * Game Routes
 */
Route::prefix('games')->middleware(['spotify.connected'])->group(function () {
    Route::get('/', [App\Http\Controllers\GameController::class, 'index'])->name('games.index');
    Route::get('/{game:slug}', [App\Http\Controllers\GameController::class, 'show'])->name('games.show');
});

/**
 * PlayStation Routes
 */
Route::prefix('playstation')->group(function () {
    Route::get('/', [App\Http\Controllers\PlayStationController::class, 'index'])->name('playstation.index');
    Route::get('/create', [App\Http\Controllers\PlayStationController::class, 'create'])->name('playstation.create');
    Route::post('/', [App\Http\Controllers\PlayStationController::class, 'store'])->name('playstation.store');
    Route::get('/sessions', [App\Http\Controllers\PlayStationController::class, 'sessions'])->name('playstation.sessions');
    Route::get('/stats', [App\Http\Controllers\PlayStationStatsController::class, 'index'])->name('playstation.stats');
    Route::get('/games/{game}', [App\Http\Controllers\PlayStationController::class, 'show'])->name('playstation.games.show');
    Route::get('/games/{game}/edit', [App\Http\Controllers\PlayStationController::class, 'edit'])->name('playstation.games.edit');
    Route::put('/games/{game}', [App\Http\Controllers\PlayStationController::class, 'update'])->name('playstation.games.update');
    Route::post('/games/{game}/convert-to-manual', [App\Http\Controllers\PlayStationController::class, 'convertToManual'])->name('playstation.games.convert-to-manual');
    Route::post('/sync', [App\Http\Controllers\PlayStationController::class, 'sync'])->name('playstation.sync');
});

/**
 * Nintendo Switch Routes
 */
Route::prefix('nintendo-switch')->group(function () {
    Route::get('/', [App\Http\Controllers\NintendoSwitchController::class, 'index'])->name('nintendo-switch.index');
    Route::get('/create', [App\Http\Controllers\NintendoSwitchController::class, 'create'])->name('nintendo-switch.create');
    Route::post('/', [App\Http\Controllers\NintendoSwitchController::class, 'store'])->name('nintendo-switch.store');
    Route::get('/sessions', [App\Http\Controllers\NintendoSwitchController::class, 'sessions'])->name('nintendo-switch.sessions');
    Route::get('/stats', [App\Http\Controllers\NintendoSwitchStatsController::class, 'index'])->name('nintendo-switch.stats');
    Route::get('/games/{game}', [App\Http\Controllers\NintendoSwitchController::class, 'show'])->name('nintendo-switch.games.show');
    Route::get('/games/{game}/edit', [App\Http\Controllers\NintendoSwitchController::class, 'edit'])->name('nintendo-switch.games.edit');
    Route::put('/games/{game}', [App\Http\Controllers\NintendoSwitchController::class, 'update'])->name('nintendo-switch.games.update');
    Route::delete('/games/{game}', [App\Http\Controllers\NintendoSwitchController::class, 'destroy'])->name('nintendo-switch.games.destroy');
    Route::post('/games/{game}/sessions', [App\Http\Controllers\NintendoSwitchSessionController::class, 'store'])->name('nintendo-switch.sessions.store');
    Route::put('/sessions/{session}', [App\Http\Controllers\NintendoSwitchSessionController::class, 'update'])->name('nintendo-switch.sessions.update');
    Route::delete('/sessions/{session}', [App\Http\Controllers\NintendoSwitchSessionController::class, 'destroy'])->name('nintendo-switch.sessions.destroy');
});

/**
 * Movie Routes
 */
Route::prefix('movies')->group(function () {
    Route::get('/', [App\Http\Controllers\MovieController::class, 'index'])->name('movies.index');
    Route::get('/stats', [App\Http\Controllers\MovieStatsController::class, 'index'])->name('movies.stats');
    Route::get('/{movie}', [App\Http\Controllers\MovieController::class, 'show'])->name('movies.show');
    Route::post('/search', [App\Http\Controllers\MovieController::class, 'search'])->name('movies.search');
    Route::post('/store', [App\Http\Controllers\MovieController::class, 'store'])->name('movies.store');
    Route::post('/{movie}/watch', [App\Http\Controllers\MovieController::class, 'markWatched'])->name('movies.watch');
    Route::delete('/watches/{watch}', [App\Http\Controllers\MovieController::class, 'deleteWatch'])->name('movies.watches.destroy');
    Route::delete('/{movie}', [App\Http\Controllers\MovieController::class, 'destroy'])->name('movies.destroy');
});

/**
 * TV Series Routes
 */
Route::prefix('tv')->group(function () {
    Route::get('/', [App\Http\Controllers\TvSeriesController::class, 'index'])->name('tv.index');
    Route::get('/stats', [App\Http\Controllers\TvStatsController::class, 'index'])->name('tv.stats');
    Route::get('/{series}', [App\Http\Controllers\TvSeriesController::class, 'show'])->name('tv.show');
    Route::post('/search', [App\Http\Controllers\TvSeriesController::class, 'search'])->name('tv.search');
    Route::post('/store', [App\Http\Controllers\TvSeriesController::class, 'store'])->name('tv.store');
    Route::post('/episodes/{episode}/watch', [App\Http\Controllers\TvSeriesController::class, 'addEpisodeWatch'])->name('tv.episodes.watch');
    Route::post('/{series}/bulk-watch', [App\Http\Controllers\TvSeriesController::class, 'bulkWatch'])->name('tv.bulk-watch');
    Route::post('/{series}/refresh', [App\Http\Controllers\TvSeriesController::class, 'refresh'])->name('tv.refresh');
    Route::delete('/watches/{watch}', [App\Http\Controllers\TvSeriesController::class, 'deleteWatch'])->name('tv.watches.destroy');
    Route::delete('/{series}', [App\Http\Controllers\TvSeriesController::class, 'destroy'])->name('tv.destroy');
});

/**
 * Steam Routes
 */
Route::prefix('steam')->group(function () {
    Route::get('/', [App\Http\Controllers\SteamController::class, 'index'])->name('steam.index');
    Route::get('/settings', [App\Http\Controllers\SteamController::class, 'settings'])->name('steam.settings');
    Route::get('/games/{game}', [App\Http\Controllers\SteamController::class, 'show'])->name('steam.games.show');
    Route::get('/games/{game}/edit', [App\Http\Controllers\SteamController::class, 'edit'])->name('steam.games.edit');
    Route::put('/games/{game}', [App\Http\Controllers\SteamController::class, 'update'])->name('steam.games.update');
    Route::post('/sync', [App\Http\Controllers\SteamController::class, 'sync'])->name('steam.sync');
    Route::post('/test-connection', [App\Http\Controllers\SteamController::class, 'testConnection'])->name('steam.test-connection');
});

/**
 * Backlog Routes
 */
Route::prefix('backlog')->group(function () {
    Route::get('/', [App\Http\Controllers\BacklogController::class, 'index'])->name('backlog.index');
    Route::patch('/{type}/{id}/status', [App\Http\Controllers\BacklogController::class, 'updateStatus'])->name('backlog.update-status');
});

/**
 * Genre Routes
 */
Route::prefix('genres')->group(function () {
    Route::post('/', [App\Http\Controllers\GenreController::class, 'store'])->name('genres.store');
});

/**
 * Recommendation Routes
 */
Route::prefix('recommend')->group(function () {
    Route::get('/{platform}', [App\Http\Controllers\RecommendationController::class, 'recommend'])->name('recommend.get');
    Route::post('/{platform}/{id}/accept', [App\Http\Controllers\RecommendationController::class, 'accept'])->name('recommend.accept');
});

/**
 * Health Routes
 */
Route::prefix('health')->group(function () {
    Route::get('/', [App\Http\Controllers\HealthController::class, 'index'])->name('health.index');
    Route::post('/', [App\Http\Controllers\HealthController::class, 'store'])->name('health.store');
    Route::get('/stats', [App\Http\Controllers\HealthStatsController::class, 'index'])->name('health.stats');
    Route::put('/{entry}', [App\Http\Controllers\HealthController::class, 'update'])->name('health.update');
    Route::delete('/{entry}', [App\Http\Controllers\HealthController::class, 'destroy'])->name('health.destroy');
});

/**
 * Job Applications Routes
 */
Route::resource('jobs', App\Http\Controllers\JobApplicationController::class)->except(['show']);

/**
 * Expenses Routes
 */
Route::prefix('expenses')->group(function () {
    Route::get('/', [App\Http\Controllers\ExpensesController::class, 'index'])->name('expenses.index');
    Route::post('/', [App\Http\Controllers\ExpensesController::class, 'store'])->name('expenses.store');
    Route::get('/stats', [App\Http\Controllers\ExpenseStatsController::class, 'index'])->name('expenses.stats');
    Route::put('/{expense}', [App\Http\Controllers\ExpensesController::class, 'update'])->name('expenses.update');
    Route::delete('/{expense}', [App\Http\Controllers\ExpensesController::class, 'destroy'])->name('expenses.destroy');

    Route::prefix('categories')->group(function () {
        Route::get('/', [App\Http\Controllers\ExpenseCategoriesController::class, 'index'])->name('expenses.categories.index');
        Route::post('/', [App\Http\Controllers\ExpenseCategoriesController::class, 'store'])->name('expenses.categories.store');
        Route::put('/{category}', [App\Http\Controllers\ExpenseCategoriesController::class, 'update'])->name('expenses.categories.update');
        Route::delete('/{category}', [App\Http\Controllers\ExpenseCategoriesController::class, 'destroy'])->name('expenses.categories.destroy');
    });
});

/**
 * Journal Routes
 */
Route::prefix('journal')->group(function () {
    Route::get('/', [App\Http\Controllers\JournalController::class, 'index'])->name('journal.index');
    Route::get('/stats', [App\Http\Controllers\JournalStatsController::class, 'index'])->name('journal.stats');
    Route::get('/create', [App\Http\Controllers\JournalController::class, 'create'])->name('journal.create');
    Route::post('/', [App\Http\Controllers\JournalController::class, 'store'])->name('journal.store');
    Route::get('/{journalEntry}', [App\Http\Controllers\JournalController::class, 'show'])->name('journal.show');
    Route::get('/{journalEntry}/edit', [App\Http\Controllers\JournalController::class, 'edit'])->name('journal.edit');
    Route::put('/{journalEntry}', [App\Http\Controllers\JournalController::class, 'update'])->name('journal.update');
    Route::delete('/{journalEntry}', [App\Http\Controllers\JournalController::class, 'destroy'])->name('journal.destroy');
});

Route::prefix('journal-tags')->group(function () {
    Route::post('/', [App\Http\Controllers\JournalTagController::class, 'store'])->name('journal.tags.store');
    Route::put('/{journalTag}', [App\Http\Controllers\JournalTagController::class, 'update'])->name('journal.tags.update');
    Route::delete('/{journalTag}', [App\Http\Controllers\JournalTagController::class, 'destroy'])->name('journal.tags.destroy');
});
