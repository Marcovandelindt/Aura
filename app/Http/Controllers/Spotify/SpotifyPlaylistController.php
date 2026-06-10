<?php

namespace App\Http\Controllers\Spotify;

use App\Http\Controllers\Controller;
use App\Http\Requests\Spotify\CreateTopTracksPlaylistRequest;
use App\Services\Spotify\SpotifyPlaylistService;
use Illuminate\Http\JsonResponse;

class SpotifyPlaylistController extends Controller
{
    public function __construct(protected SpotifyPlaylistService $playlistService) {}

    public function createTopTracks(CreateTopTracksPlaylistRequest $request): JsonResponse
    {
        try {
            $result = $this->playlistService->createTopTracksPlaylist(
                $request->validated('time_range', 'long_term')
            );

            return response()->json([
                'success' => true,
                'message' => "Playlist \"{$result['name']}\" created with {$result['track_count']} tracks!",
                'playlist_url' => $result['playlist_url'],
                'playlist_id' => $result['playlist_id'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
