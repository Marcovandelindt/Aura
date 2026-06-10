<?php

use App\Models\Setting;
use App\Services\Spotify\SpotifyPlaylistService;
use App\Services\Spotify\SpotifyService;
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
