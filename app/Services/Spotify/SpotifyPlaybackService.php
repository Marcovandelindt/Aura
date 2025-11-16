<?php

namespace App\Services\Spotify;

use Illuminate\Support\Facades\Log;
use SpotifyWebAPI\SpotifyWebAPIException;

class SpotifyPlaybackService
{
    protected SpotifyService $spotifyService;

    public function __construct(SpotifyService $spotifyService)
    {
        $this->spotifyService = $spotifyService;
    }

    /**
     * Pause playback on the user's active device
     */
    public function pause(): bool
    {
        $api = $this->spotifyService->getAuthenticatedApi();
        
        if (!$api) {
            throw new \Exception('Spotify not connected');
        }

        try {
            $api->pause();
            return true;
        } catch (SpotifyWebAPIException $e) {
            Log::error('Spotify pause error: ' . $e->getMessage());
            throw new \Exception('Failed to pause playback: ' . $e->getMessage());
        }
    }

    /**
     * Start/resume playback on the user's active device
     */
    public function play(?string $deviceId = null): bool
    {
        $api = $this->spotifyService->getAuthenticatedApi();
        
        if (!$api) {
            throw new \Exception('Spotify not connected');
        }

        try {
            $options = [];
            if ($deviceId) {
                $options['device_id'] = $deviceId;
            }
            
            $api->play(false, $options);
            return true;
        } catch (SpotifyWebAPIException $e) {
            Log::error('Spotify play error: ' . $e->getMessage());
            throw new \Exception('Failed to start playback: ' . $e->getMessage());
        }
    }

    /**
     * Skip to next track
     */
    public function skipToNext(?string $deviceId = null): bool
    {
        $api = $this->spotifyService->getAuthenticatedApi();
        
        if (!$api) {
            throw new \Exception('Spotify not connected');
        }

        try {
            $options = [];
            if ($deviceId) {
                $options['device_id'] = $deviceId;
            }
            
            $api->next($options);
            return true;
        } catch (SpotifyWebAPIException $e) {
            Log::error('Spotify next error: ' . $e->getMessage());
            throw new \Exception('Failed to skip to next track: ' . $e->getMessage());
        }
    }

    /**
     * Skip to previous track
     */
    public function skipToPrevious(?string $deviceId = null): bool
    {
        $api = $this->spotifyService->getAuthenticatedApi();
        
        if (!$api) {
            throw new \Exception('Spotify not connected');
        }

        try {
            $options = [];
            if ($deviceId) {
                $options['device_id'] = $deviceId;
            }
            
            $api->previous($options);
            return true;
        } catch (SpotifyWebAPIException $e) {
            Log::error('Spotify previous error: ' . $e->getMessage());
            throw new \Exception('Failed to skip to previous track: ' . $e->getMessage());
        }
    }

    /**
     * Toggle play/pause based on current state
     */
    public function togglePlayPause(): array
    {
        $api = $this->spotifyService->getAuthenticatedApi();
        
        if (!$api) {
            throw new \Exception('Spotify not connected');
        }

        try {
            $currentTrack = $api->getMyCurrentTrack();
            
            if (!$currentTrack) {
                throw new \Exception('No active playback found');
            }

            $isPlaying = $currentTrack->is_playing ?? false;
            
            if ($isPlaying) {
                $this->pause();
                return ['action' => 'paused', 'is_playing' => false];
            } else {
                $this->play();
                return ['action' => 'resumed', 'is_playing' => true];
            }
        } catch (SpotifyWebAPIException $e) {
            Log::error('Spotify toggle error: ' . $e->getMessage());
            throw new \Exception('Failed to toggle playback: ' . $e->getMessage());
        }
    }
}
