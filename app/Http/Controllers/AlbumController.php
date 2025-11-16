<?php

namespace App\Http\Controllers;

use App\Models\PlayedTrack;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AlbumController extends Controller
{
    /**
     * Show album details
     */
    public function show(string $albumName)
    {
        // Decode URL-encoded album name
        $albumName = urldecode($albumName);
        
        // Find all tracks from this album
        $tracksQuery = PlayedTrack::where('album_name', $albumName);
        
        // Check if album exists
        if ($tracksQuery->count() === 0) {
            abort(404, 'Album not found');
        }
        
        // Get unique tracks from this album
        $albumTracks = PlayedTrack::where('album_name', $albumName)
            ->select('spotify_track_id', 'track_name', 'artist_names', 'album_name', 'album_image_url', 'duration_ms')
            ->selectRaw('COUNT(*) as play_count')
            ->selectRaw('MAX(played_at) as last_played')
            ->groupBy('spotify_track_id', 'track_name', 'artist_names', 'album_name', 'album_image_url', 'duration_ms')
            ->orderBy('play_count', 'desc')
            ->paginate(20);
        
        // Get all plays from this album for timeline
        $allPlays = PlayedTrack::where('album_name', $albumName)
            ->orderBy('played_at', 'desc')
            ->paginate(30, ['*'], 'plays');
        
        // Calculate statistics
        $stats = [
            'total_plays' => PlayedTrack::where('album_name', $albumName)->count(),
            'unique_tracks' => PlayedTrack::where('album_name', $albumName)
                ->distinct('spotify_track_id')->count(),
            'first_played' => PlayedTrack::where('album_name', $albumName)
                ->orderBy('played_at', 'asc')->first()?->played_at,
            'last_played' => PlayedTrack::where('album_name', $albumName)
                ->orderBy('played_at', 'desc')->first()?->played_at,
            'plays_today' => PlayedTrack::where('album_name', $albumName)
                ->whereDate('played_at', Carbon::today())->count(),
            'plays_this_week' => PlayedTrack::where('album_name', $albumName)
                ->whereBetween('played_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ])->count(),
            'plays_this_month' => PlayedTrack::where('album_name', $albumName)
                ->whereMonth('played_at', Carbon::now()->month)
                ->whereYear('played_at', Carbon::now()->year)->count(),
            'total_duration_ms' => PlayedTrack::where('album_name', $albumName)
                ->sum('duration_ms'),
        ];
        
        // Get most played track from this album
        $topTrack = PlayedTrack::where('album_name', $albumName)
            ->select('spotify_track_id', 'track_name', 'artist_names', 'album_name', 'album_image_url')
            ->selectRaw('COUNT(*) as play_count')
            ->groupBy('spotify_track_id', 'track_name', 'artist_names', 'album_name', 'album_image_url')
            ->orderBy('play_count', 'desc')
            ->first();
        
        // Get unique artists from this album
        $albumArtists = PlayedTrack::where('album_name', $albumName)
            ->get()
            ->pluck('artist_names')
            ->flatten()
            ->unique()
            ->values();

        return view('albums.show', compact('albumName', 'albumTracks', 'allPlays', 'stats', 'topTrack', 'albumArtists'));
    }
}
