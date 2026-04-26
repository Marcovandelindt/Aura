<?php

namespace App\Http\Controllers;

use App\Services\Spotify\SpotifyService;
use Illuminate\View\View;

class MusicAllTimeController extends Controller
{
    public function __construct(protected SpotifyService $spotifyService) {}

    public function index(): View
    {
        $api = $this->spotifyService->getAuthenticatedApi();

        $topTracks = collect();
        $topArtists = collect();

        if ($api) {
            $tracksResponse = $api->getMyTop('tracks', ['time_range' => 'long_term', 'limit' => 50]);
            $topTracks = collect($tracksResponse->items ?? []);

            $artistsResponse = $api->getMyTop('artists', ['time_range' => 'long_term', 'limit' => 50]);
            $topArtists = collect($artistsResponse->items ?? []);
        }

        return view('music.all-time', compact('topTracks', 'topArtists'));
    }
}
