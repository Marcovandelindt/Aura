<?php

namespace App\Http\Controllers;

use App\Models\PlayedTrack;
use App\Services\Spotify\SpotifyTrackService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MusicController extends Controller
{
    protected SpotifyTrackService $trackService;

    public function __construct(SpotifyTrackService $trackService)
    {
        $this->trackService = $trackService;
    }

    /**
     * Display listening history
     */
    public function index(Request $request): View
    {
        $filter = $request->get('filter', 'all');
        $search = $request->get('search');
        
        $query = PlayedTrack::query();
        
        // Apply time filters
        switch ($filter) {
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
        
        // Apply search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('track_name', 'like', "%{$search}%")
                  ->orWhere('album_name', 'like', "%{$search}%")
                  ->orWhereJsonContains('artist_names', $search);
            });
        }
        
        // Get tracks with pagination
        $tracks = $query->orderBy('played_at', 'desc')
                       ->paginate(50)
                       ->withQueryString();
        
        // Get statistics
        $stats = $this->trackService->getStatistics($filter);
        
        // Get currently playing track
        $currentlyPlaying = $this->trackService->getCurrentlyPlaying();
        
        return view('music.index', compact('tracks', 'stats', 'filter', 'search', 'currentlyPlaying'));
    }

    /**
     * Show most played tracks
     */
    public function topTracks(Request $request): View
    {
        $period = $request->get('period', 'all');
        $limit = $request->get('limit', 50);
        
        $topTracks = PlayedTrack::mostPlayed($limit, $period);
        
        return view('music.top-tracks', compact('topTracks', 'period'));
    }

    /**
     * Force sync tracks
     */
    public function syncTracks()
    {
        try {
            $result = $this->trackService->syncRecentlyPlayed();
            
            return redirect()->route('music.index')
                ->with('success', "Synced {$result['synced']} new tracks!");
                
        } catch (\Exception $e) {
            return redirect()->route('music.index')
                ->with('error', 'Failed to sync tracks: ' . $e->getMessage());
        }
    }
}
