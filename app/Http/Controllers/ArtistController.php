<?php

namespace App\Http\Controllers;

use App\Models\PlayedTrack;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ArtistController extends Controller
{
    /**
     * Show artist details
     */
    public function show(string $artistName)
    {
        // Decode URL-encoded artist name
        $artistName = urldecode($artistName);
        
        // Find all tracks by this artist
        $tracksQuery = PlayedTrack::whereJsonContains('artist_names', $artistName);
        
        // Check if artist exists
        if ($tracksQuery->count() === 0) {
            abort(404, 'Artist not found');
        }
        
        // Get unique tracks by this artist
        $uniqueTracks = PlayedTrack::whereJsonContains('artist_names', $artistName)
            ->select('spotify_track_id', 'track_name', 'artist_names', 'album_name', 'album_image_url', 'duration_ms')
            ->selectRaw('COUNT(*) as play_count')
            ->selectRaw('MAX(played_at) as last_played')
            ->groupBy('spotify_track_id', 'track_name', 'artist_names', 'album_name', 'album_image_url', 'duration_ms')
            ->orderBy('play_count', 'desc')
            ->paginate(20);
        
        // Get all plays by this artist for timeline
        $allPlays = PlayedTrack::whereJsonContains('artist_names', $artistName)
            ->orderBy('played_at', 'desc')
            ->paginate(30, ['*'], 'plays');
        
        // Calculate statistics
        $stats = [
            'total_plays' => PlayedTrack::whereJsonContains('artist_names', $artistName)->count(),
            'unique_tracks' => PlayedTrack::whereJsonContains('artist_names', $artistName)
                ->distinct('spotify_track_id')->count(),
            'first_played' => PlayedTrack::whereJsonContains('artist_names', $artistName)
                ->orderBy('played_at', 'asc')->first()?->played_at,
            'last_played' => PlayedTrack::whereJsonContains('artist_names', $artistName)
                ->orderBy('played_at', 'desc')->first()?->played_at,
            'plays_today' => PlayedTrack::whereJsonContains('artist_names', $artistName)
                ->whereDate('played_at', Carbon::today())->count(),
            'plays_this_week' => PlayedTrack::whereJsonContains('artist_names', $artistName)
                ->whereBetween('played_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ])->count(),
            'plays_this_month' => PlayedTrack::whereJsonContains('artist_names', $artistName)
                ->whereMonth('played_at', Carbon::now()->month)
                ->whereYear('played_at', Carbon::now()->year)->count(),
            'total_duration_ms' => PlayedTrack::whereJsonContains('artist_names', $artistName)
                ->sum('duration_ms'),
        ];
        
        // Get most played track by this artist
        $topTrack = PlayedTrack::whereJsonContains('artist_names', $artistName)
            ->select('spotify_track_id', 'track_name', 'artist_names', 'album_name', 'album_image_url')
            ->selectRaw('COUNT(*) as play_count')
            ->groupBy('spotify_track_id', 'track_name', 'artist_names', 'album_name', 'album_image_url')
            ->orderBy('play_count', 'desc')
            ->first();

        return view('artists.show', compact('artistName', 'uniqueTracks', 'allPlays', 'stats', 'topTrack'));
    }
}
