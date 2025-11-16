<?php

namespace App\Http\Controllers;

use App\Services\Spotify\SpotifyService;
use App\Models\PlayedTrack;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class HomeController extends Controller 
{
    protected SpotifyService $spotifyService;

    public function __construct(SpotifyService $spotifyService)
    {
        $this->spotifyService = $spotifyService;
    }

    /**
     * Index action
     * 
     * @return View
     */
    public function index(): View
    {
        $spotifyConnected = $this->spotifyService->isConnected();
        
        // Get Songs This Week statistics
        $songsThisWeek = $this->getSongsThisWeekStats();
        
        // Get last played track for recent activity
        $lastPlayedTrack = $this->getLastPlayedTrack();
        
        return view('home.index', compact('spotifyConnected', 'songsThisWeek', 'lastPlayedTrack'));
    }
    
    /**
     * Get statistics for songs played this week
     */
    private function getSongsThisWeekStats(): array
    {
        $startOfThisWeek = Carbon::now()->startOfWeek();
        $endOfThisWeek = Carbon::now()->endOfWeek();
        $startOfLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfLastWeek = Carbon::now()->subWeek()->endOfWeek();
        
        // Count songs this week
        $thisWeekCount = PlayedTrack::whereBetween('played_at', [
            $startOfThisWeek,
            $endOfThisWeek
        ])->count();
        
        // Count songs last week for comparison
        $lastWeekCount = PlayedTrack::whereBetween('played_at', [
            $startOfLastWeek,
            $endOfLastWeek
        ])->count();
        
        // Calculate difference
        $difference = $thisWeekCount - $lastWeekCount;
        $changeText = '';
        $changeClass = '';
        
        if ($difference > 0) {
            $changeText = "+{$difference} from last week";
            $changeClass = 'positive';
        } elseif ($difference < 0) {
            $changeText = abs($difference) . " fewer than last week";
            $changeClass = 'negative';
        } else {
            $changeText = "Same as last week";
            $changeClass = '';
        }
        
        return [
            'count' => $thisWeekCount,
            'change_text' => $changeText,
            'change_class' => $changeClass,
            'last_week_count' => $lastWeekCount
        ];
    }
    
    /**
     * Get the last played track for recent activity
     */
    private function getLastPlayedTrack(): ?PlayedTrack
    {
        return PlayedTrack::orderBy('played_at', 'desc')->first();
    }
}