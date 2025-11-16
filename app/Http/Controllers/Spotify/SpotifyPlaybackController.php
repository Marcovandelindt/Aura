<?php

namespace App\Http\Controllers\Spotify;

use App\Http\Controllers\Controller;
use App\Services\Spotify\SpotifyPlaybackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpotifyPlaybackController extends Controller
{
    protected SpotifyPlaybackService $playbackService;

    public function __construct(SpotifyPlaybackService $playbackService)
    {
        $this->playbackService = $playbackService;
    }

    /**
     * Toggle play/pause
     */
    public function togglePlayPause(): JsonResponse
    {
        try {
            $result = $this->playbackService->togglePlayPause();
            
            return response()->json([
                'success' => true,
                'action' => $result['action'],
                'is_playing' => $result['is_playing'],
                'message' => $result['action'] === 'paused' ? 'Playback paused' : 'Playback resumed'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Skip to next track
     */
    public function skipToNext(): JsonResponse
    {
        try {
            $this->playbackService->skipToNext();
            
            return response()->json([
                'success' => true,
                'message' => 'Skipped to next track'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Skip to previous track
     */
    public function skipToPrevious(): JsonResponse
    {
        try {
            $this->playbackService->skipToPrevious();
            
            return response()->json([
                'success' => true,
                'message' => 'Skipped to previous track'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
