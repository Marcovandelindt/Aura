<?php

namespace App\Http\Controllers;

use App\Models\PlayedTrack;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class MusicStatsController extends Controller
{
    /**
     * Show music statistics page
     */
    public function index(): View
    {
        $stats = [
            'top_artists' => $this->getTopArtists(),
            'top_tracks' => $this->getTopTracks(),
            'top_albums' => $this->getTopAlbums(),
            'listening_stats' => $this->getListeningStats(),
        ];
        
        return view('music.stats', compact('stats'));
    }
    
    /**
     * Get top artists by play count
     */
    private function getTopArtists(int $limit = 10): array
    {
        $artistStats = [];
        
        // Get all played tracks and count artist plays
        $tracks = PlayedTrack::select('artist_names', 'played_at', 'duration_ms', 'spotify_track_id')->get();
        
        foreach ($tracks as $track) {
            foreach ($track->artist_names as $artist) {
                if (!isset($artistStats[$artist])) {
                    $artistStats[$artist] = [
                        'name' => $artist,
                        'play_count' => 0,
                        'total_duration_ms' => 0,
                        'unique_tracks' => [],
                        'first_played' => null,
                        'last_played' => null,
                    ];
                }
                
                $artistStats[$artist]['play_count']++;
                $artistStats[$artist]['total_duration_ms'] += $track->duration_ms;
                $artistStats[$artist]['unique_tracks'][$track->spotify_track_id] = true;
                
                if (!$artistStats[$artist]['first_played'] || $track->played_at < $artistStats[$artist]['first_played']) {
                    $artistStats[$artist]['first_played'] = $track->played_at;
                }
                
                if (!$artistStats[$artist]['last_played'] || $track->played_at > $artistStats[$artist]['last_played']) {
                    $artistStats[$artist]['last_played'] = $track->played_at;
                }
            }
        }
        
        // Convert unique tracks count and sort by play count
        foreach ($artistStats as &$stats) {
            $stats['unique_tracks_count'] = count($stats['unique_tracks']);
            unset($stats['unique_tracks']);
        }
        
        usort($artistStats, function($a, $b) {
            return $b['play_count'] <=> $a['play_count'];
        });
        
        return array_slice($artistStats, 0, $limit);
    }
    
    /**
     * Get top tracks by play count
     */
    private function getTopTracks(int $limit = 10): array
    {
        return PlayedTrack::select('spotify_track_id', 'track_name', 'artist_names', 'album_name', 'album_image_url', 'duration_ms')
            ->selectRaw('COUNT(*) as play_count')
            ->selectRaw('MAX(played_at) as last_played')
            ->selectRaw('MIN(played_at) as first_played')
            ->groupBy('spotify_track_id', 'track_name', 'artist_names', 'album_name', 'album_image_url', 'duration_ms')
            ->orderBy('play_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($track) {
                $track->artists_string = implode(', ', $track->artist_names);
                return $track;
            })
            ->toArray();
    }
    
    /**
     * Get top albums by play count
     */
    private function getTopAlbums(int $limit = 10): array
    {
        return PlayedTrack::select('album_name', 'artist_names', 'album_image_url')
            ->selectRaw('COUNT(*) as play_count')
            ->selectRaw('COUNT(DISTINCT spotify_track_id) as unique_tracks_count')
            ->selectRaw('MAX(played_at) as last_played')
            ->selectRaw('MIN(played_at) as first_played')
            ->groupBy('album_name', 'artist_names', 'album_image_url')
            ->orderBy('play_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($album) {
                $album->artists_string = implode(', ', array_unique($album->artist_names));
                return $album;
            })
            ->toArray();
    }
    
    /**
     * Get general listening statistics
     */
    private function getListeningStats(): array
    {
        $totalTracks = PlayedTrack::count();
        $uniqueTracks = PlayedTrack::distinct('spotify_track_id')->count();
        $totalDuration = PlayedTrack::sum('duration_ms');
        
        $firstTrack = PlayedTrack::orderBy('played_at', 'asc')->first();
        $lastTrack = PlayedTrack::orderBy('played_at', 'desc')->first();
        
        // Stats for different time periods
        $todayTracks = PlayedTrack::whereDate('played_at', Carbon::today())->count();
        $weekTracks = PlayedTrack::whereBetween('played_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ])->count();
        $monthTracks = PlayedTrack::whereMonth('played_at', Carbon::now()->month)
            ->whereYear('played_at', Carbon::now()->year)
            ->count();
        
        return [
            'total_tracks' => $totalTracks,
            'unique_tracks' => $uniqueTracks,
            'total_duration_ms' => $totalDuration,
            'total_duration_hours' => round($totalDuration / 1000 / 60 / 60, 1),
            'first_track_date' => $firstTrack?->played_at,
            'last_track_date' => $lastTrack?->played_at,
            'tracks_today' => $todayTracks,
            'tracks_this_week' => $weekTracks,
            'tracks_this_month' => $monthTracks,
            'average_per_day' => $firstTrack ? round($totalTracks / max(1, $firstTrack->played_at->diffInDays(Carbon::now())), 1) : 0,
        ];
    }
}
