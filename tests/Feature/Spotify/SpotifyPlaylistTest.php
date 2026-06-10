<?php

use App\Models\Album;
use App\Models\Play;
use App\Models\Setting;
use App\Models\Track;
use App\Services\Spotify\SpotifyPlaylistService;
use App\Services\Spotify\SpotifyService;
use Illuminate\Support\Carbon;
use SpotifyWebAPI\SpotifyWebAPI;

use function Pest\Laravel\mock;
use function Pest\Laravel\postJson;

beforeEach(function () {
    Setting::set('spotify_refresh_token', 'fake-refresh-token');
    Setting::set('spotify_id', 'fake-spotify-user-id');
});

afterEach(function () {
    Setting::remove('spotify_refresh_token');
    Setting::remove('spotify_id');
});

it('creates a top tracks playlist for the default time range', function () {
    $api = mock(SpotifyWebAPI::class);

    $api->shouldReceive('getMyTop')
        ->with('tracks', ['limit' => 50, 'time_range' => 'long_term'])
        ->andReturn((object) [
            'items' => [
                (object) ['uri' => 'spotify:track:aaa'],
                (object) ['uri' => 'spotify:track:bbb'],
            ],
        ]);

    $api->shouldReceive('createPlaylist')
        ->with('fake-spotify-user-id', \Mockery::subset(['name' => 'My Top 50 — All Time']))
        ->andReturn((object) [
            'id' => 'playlist-123',
            'external_urls' => (object) ['spotify' => 'https://open.spotify.com/playlist/playlist-123'],
        ]);

    $api->shouldReceive('addPlaylistTracks')
        ->with('playlist-123', ['spotify:track:aaa', 'spotify:track:bbb'])
        ->once();

    $spotifyService = mock(SpotifyService::class);
    $spotifyService->shouldReceive('getAuthenticatedApi')->andReturn($api);
    $spotifyService->shouldReceive('isConnected')->andReturn(true);

    $service = new SpotifyPlaylistService($spotifyService);
    $result = $service->createTopTracksPlaylist('long_term');

    expect($result['playlist_id'])->toBe('playlist-123')
        ->and($result['track_count'])->toBe(2)
        ->and($result['name'])->toBe('My Top 50 — All Time')
        ->and($result['playlist_url'])->toBe('https://open.spotify.com/playlist/playlist-123');
});

it('uses correct playlist name per time range', function (string $timeRange, string $expectedName) {
    $api = mock(SpotifyWebAPI::class);

    $api->shouldReceive('getMyTop')
        ->andReturn((object) ['items' => [(object) ['uri' => 'spotify:track:aaa']]]);

    $api->shouldReceive('createPlaylist')
        ->with(\Mockery::any(), \Mockery::subset(['name' => $expectedName]))
        ->andReturn((object) [
            'id' => 'playlist-123',
            'external_urls' => (object) ['spotify' => 'https://open.spotify.com/playlist/playlist-123'],
        ]);

    $api->shouldReceive('addPlaylistTracks')->once();

    $spotifyService = mock(SpotifyService::class);
    $spotifyService->shouldReceive('getAuthenticatedApi')->andReturn($api);

    $result = (new SpotifyPlaylistService($spotifyService))->createTopTracksPlaylist($timeRange);

    expect($result['name'])->toBe($expectedName);
})->with([
    'short_term' => ['short_term', 'My Top 50 — Last 4 Weeks'],
    'medium_term' => ['medium_term', 'My Top 50 — Last 6 Months'],
    'long_term' => ['long_term', 'My Top 50 — All Time'],
]);

it('throws when spotify is not connected', function () {
    $spotifyService = mock(SpotifyService::class);
    $spotifyService->shouldReceive('getAuthenticatedApi')->andReturn(null);

    expect(fn () => (new SpotifyPlaylistService($spotifyService))->createTopTracksPlaylist())
        ->toThrow(\Exception::class, 'Spotify not connected');
});

it('throws when no top tracks are found', function () {
    $api = mock(SpotifyWebAPI::class);
    $api->shouldReceive('getMyTop')->andReturn((object) ['items' => []]);

    $spotifyService = mock(SpotifyService::class);
    $spotifyService->shouldReceive('getAuthenticatedApi')->andReturn($api);

    expect(fn () => (new SpotifyPlaylistService($spotifyService))->createTopTracksPlaylist())
        ->toThrow(\Exception::class, 'No top tracks found');
});

it('returns 400 when the route is hit without spotify connected', function () {
    Setting::remove('spotify_refresh_token');

    postJson(route('spotify.playlists.top-tracks'))
        ->assertRedirect(route('spotify.auth'));
});

it('validates time_range on the route', function () {
    $spotifyService = mock(SpotifyService::class);
    $spotifyService->shouldReceive('isConnected')->andReturn(true);
    app()->instance(SpotifyService::class, $spotifyService);

    postJson(route('spotify.playlists.top-tracks'), ['time_range' => 'invalid_range'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['time_range']);
});

// --- Date range playlist ---

it('creates a playlist from local play history for a date range', function () {
    $album = Album::create(['name' => 'Test Album', 'image_url' => null]);
    $track = Track::create([
        'title' => 'Test Track',
        'spotify_track_id' => 'track123',
        'album_id' => $album->id,
        'spotify_uri' => 'spotify:track:track123',
        'duration_ms' => 200000,
    ]);
    Play::create(['track_id' => $track->id, 'played_at' => Carbon::parse('2024-06-15'), 'source' => 'spotify']);
    Play::create(['track_id' => $track->id, 'played_at' => Carbon::parse('2024-06-20'), 'source' => 'spotify']);

    $api = mock(SpotifyWebAPI::class);
    $api->shouldReceive('createPlaylist')
        ->withArgs(fn ($userId, $opts) => str_contains($opts['name'], '01 Jun 2024') && str_contains($opts['name'], '30 Jun 2024'))
        ->andReturn((object) [
            'id' => 'pl-456',
            'external_urls' => (object) ['spotify' => 'https://open.spotify.com/playlist/pl-456'],
        ]);
    $api->shouldReceive('addPlaylistTracks')
        ->with('pl-456', ['spotify:track:track123'])
        ->once();

    $spotifyService = mock(SpotifyService::class);
    $spotifyService->shouldReceive('getAuthenticatedApi')->andReturn($api);

    $result = (new SpotifyPlaylistService($spotifyService))
        ->createDateRangePlaylist(Carbon::parse('2024-06-01'), Carbon::parse('2024-06-30'));

    expect($result['track_count'])->toBe(1)
        ->and($result['playlist_id'])->toBe('pl-456');
});

it('throws when no plays exist in the date range', function () {
    $spotifyService = mock(SpotifyService::class);
    $spotifyService->shouldReceive('getAuthenticatedApi')->andReturn(mock(SpotifyWebAPI::class));

    expect(fn () => (new SpotifyPlaylistService($spotifyService))
        ->createDateRangePlaylist(Carbon::parse('2020-01-01'), Carbon::parse('2020-01-31'))
    )->toThrow(\Exception::class, 'No plays found');
});

it('validates start_date and end_date on the date range route', function () {
    $spotifyService = mock(SpotifyService::class);
    $spotifyService->shouldReceive('isConnected')->andReturn(true);
    app()->instance(SpotifyService::class, $spotifyService);

    postJson(route('spotify.playlists.date-range'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['start_date', 'end_date']);
});

it('rejects end_date in the future on the date range route', function () {
    $spotifyService = mock(SpotifyService::class);
    $spotifyService->shouldReceive('isConnected')->andReturn(true);
    app()->instance(SpotifyService::class, $spotifyService);

    postJson(route('spotify.playlists.date-range'), [
        'start_date' => '2024-01-01',
        'end_date' => Carbon::now()->addDay()->toDateString(),
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['end_date']);
});

it('rejects start_date after end_date on the date range route', function () {
    $spotifyService = mock(SpotifyService::class);
    $spotifyService->shouldReceive('isConnected')->andReturn(true);
    app()->instance(SpotifyService::class, $spotifyService);

    postJson(route('spotify.playlists.date-range'), [
        'start_date' => '2024-06-30',
        'end_date' => '2024-06-01',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['start_date']);
});
