<?php

namespace App\View\Composers;

use App\Services\Spotify\SpotifyService;
use App\Services\Spotify\SpotifyTrackService;
use Illuminate\View\View;

class SpotifyComposer
{
    protected SpotifyService $spotifyService;
    protected SpotifyTrackService $trackService;

    public function __construct(SpotifyService $spotifyService, SpotifyTrackService $trackService)
    {
        $this->spotifyService = $spotifyService;
        $this->trackService = $trackService;
    }

    public function compose(View $view): void
    {
        $spotifyConnected = $this->spotifyService->isConnected();
        $currentlyPlaying = null;

        if ($spotifyConnected) {
            try {
                $currentlyPlaying = $this->trackService->getCurrentlyPlaying();
            } catch (\Exception $e) {
                // Fail silently
            }
        }

        $view->with([
            'globalSpotifyConnected' => $spotifyConnected,
            'globalCurrentlyPlaying' => $currentlyPlaying,
        ]);
    }
}