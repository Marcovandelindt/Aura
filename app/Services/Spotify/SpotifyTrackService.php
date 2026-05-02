<?php

namespace App\Services\Spotify;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Play;
use App\Models\Track;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use SpotifyWebAPI\SpotifyWebAPIException;

class SpotifyTrackService
{
    public function __construct(protected SpotifyService $spotifyService) {}

    public function syncRecentlyPlayed(): array
    {
        $api = $this->spotifyService->getAuthenticatedApi();

        if (! $api) {
            throw new \Exception('Spotify not connected');
        }

        try {
            $recentlyPlayed = $api->getMyRecentTracks(['limit' => 50]);

            $synced = 0;
            $skipped = 0;

            foreach ($recentlyPlayed->items as $item) {
                $spotifyTrack = $item->track;
                $playedAt = Carbon::parse($item->played_at);

                $album = null;
                if (! empty($spotifyTrack->album->name)) {
                    $album = Album::firstOrCreate(
                        ['name' => $spotifyTrack->album->name],
                        ['image_url' => $spotifyTrack->album->images[0]->url ?? null],
                    );
                }

                $track = Track::firstOrCreate(
                    ['spotify_track_id' => $spotifyTrack->id],
                    [
                        'title' => $spotifyTrack->name,
                        'album_id' => $album?->id,
                        'duration_ms' => $spotifyTrack->duration_ms,
                        'popularity' => $spotifyTrack->popularity,
                        'preview_url' => $spotifyTrack->preview_url,
                        'spotify_uri' => $spotifyTrack->uri,
                    ],
                );

                if (! $track->artists()->exists()) {
                    foreach ($spotifyTrack->artists as $index => $artistData) {
                        $artist = Artist::firstOrCreate(['name' => $artistData->name]);
                        $track->artists()->syncWithoutDetaching([
                            $artist->id => ['is_primary' => $index === 0, 'sort_order' => $index],
                        ]);
                    }
                }

                $context = isset($item->context) ? [
                    'type' => $item->context->type ?? null,
                    'uri' => $item->context->uri ?? null,
                ] : null;

                $inserted = Play::insertOrIgnore([[
                    'track_id' => $track->id,
                    'played_at' => $playedAt,
                    'source' => 'spotify',
                    'context' => $context ? json_encode($context) : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]]);

                if ($inserted) {
                    $synced++;
                } else {
                    $skipped++;
                }
            }

            Log::info("Spotify sync completed: {$synced} tracks synced, {$skipped} duplicates skipped");

            return [
                'synced' => $synced,
                'skipped' => $skipped,
                'total' => count($recentlyPlayed->items),
            ];

        } catch (SpotifyWebAPIException $e) {
            Log::error('Spotify API error: '.$e->getMessage());

            if (str_contains($e->getMessage(), 'access token') || str_contains($e->getMessage(), 'expired')) {
                throw new \Exception('Spotify authentication expired. Please reconnect your Spotify account.');
            }

            throw new \Exception('Failed to fetch recently played tracks: '.$e->getMessage());
        }
    }

    public function getCurrentlyPlaying(): ?object
    {
        $api = $this->spotifyService->getAuthenticatedApi();

        if (! $api) {
            return null;
        }

        try {
            $currentTrack = $api->getMyCurrentTrack();

            if (! $currentTrack || ! $currentTrack->item) {
                return null;
            }

            return (object) [
                'track_name' => $currentTrack->item->name,
                'artist_names' => array_map(fn ($artist) => $artist->name, $currentTrack->item->artists),
                'album_name' => $currentTrack->item->album->name,
                'album_image_url' => $currentTrack->item->album->images[0]->url ?? null,
                'is_playing' => $currentTrack->is_playing,
                'progress_ms' => $currentTrack->progress_ms ?? 0,
                'duration_ms' => $currentTrack->item->duration_ms,
                'spotify_uri' => $currentTrack->item->uri,
                'external_url' => $currentTrack->item->external_urls->spotify ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get currently playing track: '.$e->getMessage());

            return null;
        }
    }
}
