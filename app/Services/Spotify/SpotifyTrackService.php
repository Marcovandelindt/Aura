<?php

namespace App\Services\Spotify;

use App\Models\PlayedTrack;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use SpotifyWebAPI\SpotifyWebAPIException;

class SpotifyTrackService
{
    protected SpotifyService $spotifyService;

    public function __construct(SpotifyService $spotifyService)
    {
        $this->spotifyService = $spotifyService;
    }

    /**
     * Sync recently played tracks from Spotify
     */
    public function syncRecentlyPlayed(): array
    {
        $api = $this->spotifyService->getAuthenticatedApi();
        
        if (!$api) {
            throw new \Exception('Spotify not connected');
        }

        try {
            // Get recently played tracks (max 50)
            $options = [
                'limit' => 50,
            ];
            
            $recentlyPlayed = $api->getMyRecentTracks($options);
            
            $synced = 0;
            $skipped = 0;
            
            foreach ($recentlyPlayed->items as $item) {
                $playedAt = Carbon::parse($item->played_at);
                $track = $item->track;
                
                // Extract artist names
                $artistNames = array_map(function($artist) {
                    return $artist->name;
                }, $track->artists);
                
                // Get the best quality album image
                $albumImage = null;
                if (!empty($track->album->images)) {
                    $albumImage = $track->album->images[0]->url;
                }
                
                // Prepare track data
                $trackData = [
                    'spotify_track_id' => $track->id,
                    'played_at' => $playedAt,
                    'track_name' => $track->name,
                    'artist_names' => $artistNames,
                    'album_name' => $track->album->name,
                    'album_image_url' => $albumImage,
                    'duration_ms' => $track->duration_ms,
                    'popularity' => $track->popularity,
                    'preview_url' => $track->preview_url,
                    'spotify_uri' => $track->uri,
                    'contexts' => isset($item->context) ? [
                        'type' => $item->context->type ?? null,
                        'uri' => $item->context->uri ?? null,
                    ] : null,
                ];
                
                // Try to insert, skip if duplicate
                try {
                    PlayedTrack::create($trackData);
                    $synced++;
                } catch (\Illuminate\Database\QueryException $e) {
                    if ($e->getCode() == '23000') { // Duplicate entry
                        $skipped++;
                    } else {
                        throw $e;
                    }
                }
            }
            
            Log::info("Spotify sync completed: {$synced} tracks synced, {$skipped} duplicates skipped");
            
            return [
                'synced' => $synced,
                'skipped' => $skipped,
                'total' => count($recentlyPlayed->items),
            ];
            
        } catch (SpotifyWebAPIException $e) {
            Log::error('Spotify API error: ' . $e->getMessage());
            throw new \Exception('Failed to fetch recently played tracks: ' . $e->getMessage());
        }
    }

    /**
     * Get listening statistics
     */
    public function getStatistics(string $period = 'all'): array
    {
        $query = PlayedTrack::query();
        
        switch ($period) {
            case 'today':
                $query->today();
                break;
            case 'week':
                $query->thisWeek();
                break;
            case 'month':
                $query->thisMonth();
                break;
        }
        
        // Clone query for different calculations to avoid conflicts
        $baseQuery = clone $query;
        $countQuery = clone $query;
        $durationQuery = clone $query;
        $firstTrackQuery = clone $query;
        
        $firstTrack = $firstTrackQuery->orderBy('played_at', 'asc')->first();
        
        return [
            'total_tracks' => $baseQuery->count(),
            'unique_tracks' => $countQuery->distinct('spotify_track_id')->count(),
            'total_duration_ms' => $durationQuery->sum('duration_ms'),
            'most_played' => PlayedTrack::mostPlayed(5, $period),
            'data_since' => $firstTrack?->played_at,
        ];
    }

    /**
     * Search tracks in history
     */
    public function searchHistory(string $query, int $limit = 50)
    {
        return PlayedTrack::where(function($q) use ($query) {
            $q->where('track_name', 'like', "%{$query}%")
              ->orWhere('album_name', 'like', "%{$query}%")
              ->orWhereJsonContains('artist_names', $query);
        })
        ->orderBy('played_at', 'desc')
        ->limit($limit)
        ->get();
    }

    /**
     * Get currently playing track
     */
    public function getCurrentlyPlaying(): ?object
    {
        $api = $this->spotifyService->getAuthenticatedApi();
        
        if (!$api) {
            return null;
        }

        try {
            $currentTrack = $api->getMyCurrentTrack();
            
            if (!$currentTrack || !$currentTrack->item) {
                return null;
            }

            return (object) [
                'track_name' => $currentTrack->item->name,
                'artist_names' => array_map(fn($artist) => $artist->name, $currentTrack->item->artists),
                'album_name' => $currentTrack->item->album->name,
                'album_image_url' => $currentTrack->item->album->images[0]->url ?? null,
                'is_playing' => $currentTrack->is_playing,
                'progress_ms' => $currentTrack->progress_ms ?? 0,
                'duration_ms' => $currentTrack->item->duration_ms,
                'spotify_uri' => $currentTrack->item->uri,
                'external_url' => $currentTrack->item->external_urls->spotify ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get currently playing track: ' . $e->getMessage());
            return null;
        }
    }
}