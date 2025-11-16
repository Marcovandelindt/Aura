<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TrackController extends Controller
{
    /**
     * Show track details
     */
    public function show(string $spotifyTrackId)
    {
        // Find all played tracks with this spotify_track_id
        $playedTracksQuery = \App\Models\PlayedTrack::where('spotify_track_id', $spotifyTrackId);
        
        // Get the first played track to use for track info
        $firstPlayedTrack = $playedTracksQuery->first();
        
        if (!$firstPlayedTrack) {
            abort(404, 'Track not found');
        }
        
        // Create a track object with the data we have
        $track = (object) [
            'spotify_track_id' => $firstPlayedTrack->spotify_track_id,
            'track_name' => $firstPlayedTrack->track_name,
            'artist_names' => $firstPlayedTrack->artist_names,
            'artists_string' => implode(', ', $firstPlayedTrack->artist_names),
            'album_name' => $firstPlayedTrack->album_name,
            'album_image_url' => $firstPlayedTrack->album_image_url,
            'duration_ms' => $firstPlayedTrack->duration_ms,
            'formatted_duration' => sprintf('%d:%02d', 
                floor($firstPlayedTrack->duration_ms / 1000 / 60), 
                floor(($firstPlayedTrack->duration_ms / 1000) % 60)
            ),
            'popularity' => $firstPlayedTrack->popularity,
            'spotify_uri' => $firstPlayedTrack->spotify_uri,
            'genres' => null, // Will add later when we implement genres
        ];
        
        // Load played tracks with latest first
        $playedTracks = \App\Models\PlayedTrack::where('spotify_track_id', $spotifyTrackId)
            ->orderBy('played_at', 'desc')
            ->paginate(20);

        // Calculate statistics - use fresh queries for each stat
        $stats = [
            'total_plays' => \App\Models\PlayedTrack::where('spotify_track_id', $spotifyTrackId)->count(),
            'first_played' => \App\Models\PlayedTrack::where('spotify_track_id', $spotifyTrackId)->orderBy('played_at', 'asc')->first()?->played_at,
            'last_played' => \App\Models\PlayedTrack::where('spotify_track_id', $spotifyTrackId)->orderBy('played_at', 'desc')->first()?->played_at,
            'plays_today' => \App\Models\PlayedTrack::where('spotify_track_id', $spotifyTrackId)->whereDate('played_at', Carbon::today())->count(),
            'plays_this_week' => \App\Models\PlayedTrack::where('spotify_track_id', $spotifyTrackId)->whereBetween('played_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count(),
            'plays_this_month' => \App\Models\PlayedTrack::where('spotify_track_id', $spotifyTrackId)->whereMonth('played_at', Carbon::now()->month)
                ->whereYear('played_at', Carbon::now()->year)->count(),
        ];

        return view('tracks.show', compact('track', 'playedTracks', 'stats'));
    }
}
