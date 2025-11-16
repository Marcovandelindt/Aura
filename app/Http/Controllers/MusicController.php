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
        
        // Get moods for all tracks
        $trackIds = $tracks->pluck('spotify_track_id')->unique()->toArray();
        $trackMoods = PlayedTrack::getMoodsForTracks($trackIds);
        
        // Add moods to track objects
        foreach ($tracks as $track) {
            $track->moods = $trackMoods[$track->spotify_track_id] ?? [];
        }
        
        // Get statistics
        $stats = $this->trackService->getStatistics($filter);
        
        // Get currently playing track
        $currentlyPlaying = $this->trackService->getCurrentlyPlaying();
        
        // Get top artist this week
        $topArtistThisWeek = $this->getTopArtistThisWeek();
        
        return view('music.index', compact('tracks', 'stats', 'filter', 'search', 'currentlyPlaying', 'topArtistThisWeek'));
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
    
    /**
     * Get top artist this week with stats
     */
    private function getTopArtistThisWeek(): ?object
    {
        $startOfWeek = \Illuminate\Support\Carbon::now()->startOfWeek();
        $endOfWeek = \Illuminate\Support\Carbon::now()->endOfWeek();
        
        // Get all tracks played this week
        $tracksThisWeek = PlayedTrack::whereBetween('played_at', [$startOfWeek, $endOfWeek])
            ->get();
        
        if ($tracksThisWeek->isEmpty()) {
            return null;
        }
        
        // Count plays per artist
        $artistStats = [];
        foreach ($tracksThisWeek as $track) {
            foreach ($track->artist_names as $artistName) {
                if (!isset($artistStats[$artistName])) {
                    $artistStats[$artistName] = [
                        'plays' => 0,
                        'unique_tracks' => [],
                        'total_duration_ms' => 0,
                        'album_images' => [],
                        'first_track' => null,
                        'daily_plays' => [],
                        'hourly_plays' => [],
                        'play_dates' => []
                    ];
                }
                
                $artistStats[$artistName]['plays']++;
                $artistStats[$artistName]['unique_tracks'][$track->spotify_track_id] = $track->track_name;
                $artistStats[$artistName]['total_duration_ms'] += $track->duration_ms;
                
                // Track daily plays
                $dayName = $track->played_at->format('l'); // Monday, Tuesday, etc.
                if (!isset($artistStats[$artistName]['daily_plays'][$dayName])) {
                    $artistStats[$artistName]['daily_plays'][$dayName] = 0;
                }
                $artistStats[$artistName]['daily_plays'][$dayName]++;
                
                // Track hourly plays (0-23)
                $hour = $track->played_at->format('H');
                if (!isset($artistStats[$artistName]['hourly_plays'][$hour])) {
                    $artistStats[$artistName]['hourly_plays'][$hour] = 0;
                }
                $artistStats[$artistName]['hourly_plays'][$hour]++;
                
                // Track play dates for streak calculation
                $playDate = $track->played_at->format('Y-m-d');
                if (!in_array($playDate, $artistStats[$artistName]['play_dates'])) {
                    $artistStats[$artistName]['play_dates'][] = $playDate;
                }
                
                if ($track->album_image_url && !in_array($track->album_image_url, $artistStats[$artistName]['album_images'])) {
                    $artistStats[$artistName]['album_images'][] = $track->album_image_url;
                }
                
                if (!$artistStats[$artistName]['first_track']) {
                    $artistStats[$artistName]['first_track'] = $track;
                }
            }
        }
        
        if (empty($artistStats)) {
            return null;
        }
        
        // Sort by plays and get top artist
        uasort($artistStats, function($a, $b) {
            return $b['plays'] <=> $a['plays'];
        });
        
        $topArtistName = array_key_first($artistStats);
        $topArtistData = $artistStats[$topArtistName];
        
        // Find the day with most plays
        $topDay = null;
        $maxPlays = 0;
        foreach ($topArtistData['daily_plays'] as $day => $plays) {
            if ($plays > $maxPlays) {
                $maxPlays = $plays;
                $topDay = $day;
            }
        }
        
        // Calculate peak hour
        $peakHour = null;
        $maxHourlyPlays = 0;
        foreach ($topArtistData['hourly_plays'] as $hour => $plays) {
            if ($plays > $maxHourlyPlays) {
                $maxHourlyPlays = $plays;
                $peakHour = $hour;
            }
        }
        
        // Calculate streak (consecutive days)
        $playDates = $topArtistData['play_dates'];
        sort($playDates);
        $streak = 0;
        $currentStreak = 1;
        
        if (count($playDates) > 0) {
            for ($i = 1; $i < count($playDates); $i++) {
                $prevDate = \Illuminate\Support\Carbon::parse($playDates[$i - 1]);
                $currentDate = \Illuminate\Support\Carbon::parse($playDates[$i]);
                
                if ($currentDate->diffInDays($prevDate) === 1) {
                    $currentStreak++;
                } else {
                    $currentStreak = 1;
                }
                
                $streak = max($streak, $currentStreak);
            }
            
            // If only one day, streak is 1
            if (count($playDates) === 1) {
                $streak = 1;
            }
        }
        
        // Format peak hour for display
        $peakHourFormatted = null;
        if ($peakHour !== null) {
            $hour = (int)$peakHour;
            if ($hour === 0) {
                $peakHourFormatted = '12 AM';
            } elseif ($hour < 12) {
                $peakHourFormatted = $hour . ' AM';
            } elseif ($hour === 12) {
                $peakHourFormatted = '12 PM';
            } else {
                $peakHourFormatted = ($hour - 12) . ' PM';
            }
        }
        
        return (object) [
            'name' => $topArtistName,
            'plays' => $topArtistData['plays'],
            'unique_tracks_count' => count($topArtistData['unique_tracks']),
            'unique_tracks' => array_values($topArtistData['unique_tracks']),
            'total_duration_ms' => $topArtistData['total_duration_ms'],
            'album_image_url' => $topArtistData['album_images'][0] ?? null,
            'sample_track' => $topArtistData['first_track'],
            'top_day' => $topDay,
            'top_day_plays' => $maxPlays,
            'daily_plays' => $topArtistData['daily_plays'],
            'streak_days' => $streak,
            'peak_hour' => $peakHourFormatted,
            'peak_hour_plays' => $maxHourlyPlays
        ];
    }
}
