<?php

namespace App\Jobs;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Track;
use App\Services\Spotify\SpotifyService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EnrichSpotifyTracks implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(public array $spotifyTrackIds) {}

    public function handle(SpotifyService $spotifyService): void
    {
        $api = $spotifyService->getAuthenticatedApi();

        if (! $api) {
            $this->fail('Spotify not connected.');

            return;
        }

        $result = $api->getTracks($this->spotifyTrackIds);

        foreach ($result->tracks ?? [] as $spotifyTrack) {
            if (! $spotifyTrack) {
                continue;
            }

            $track = Track::where('spotify_track_id', $spotifyTrack->id)->first();

            if (! $track) {
                continue;
            }

            $album = null;
            if (! empty($spotifyTrack->album->name)) {
                $album = Album::updateOrCreate(
                    ['name' => $spotifyTrack->album->name],
                    [
                        'image_url' => $spotifyTrack->album->images[0]->url ?? null,
                        'release_date' => $spotifyTrack->album->release_date ?? null,
                    ]
                );
            }

            $track->update([
                'title' => $spotifyTrack->name,
                'album_id' => $album?->id,
                'duration_ms' => $spotifyTrack->duration_ms,
                'popularity' => $spotifyTrack->popularity,
                'preview_url' => $spotifyTrack->preview_url ?? null,
                'spotify_uri' => $spotifyTrack->uri,
            ]);

            // Replace stub artist(s) with full artist list from API
            $track->artists()->detach();
            foreach ($spotifyTrack->artists as $index => $artistData) {
                $artist = Artist::firstOrCreate(['name' => $artistData->name]);
                $track->artists()->attach($artist->id, [
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }
        }
    }
}
