<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\PlayedTrack;
use Carbon\Carbon;
use Illuminate\View\View;

class GameController extends Controller
{
    public function index(): View
    {
        $games = Game::query()
            ->active()
            ->popular()
            ->get();

        return view('games.index', compact('games'));
    }

    public function show(Game $game): View
    {
        // Get all tracks associated with this game through the played_track_mood pivot
        $tracks = PlayedTrack::query()
            ->select('played_tracks.spotify_track_id', 'played_tracks.track_name', 'played_tracks.artist_names',
                'played_tracks.album_name', 'played_tracks.album_image_url', 'played_tracks.duration_ms')
            ->selectRaw('COUNT(*) as play_count')
            ->selectRaw('MAX(played_tracks.played_at) as last_played')
            ->selectRaw('MIN(played_tracks.played_at) as first_played')
            ->join('played_track_mood', 'played_tracks.spotify_track_id', '=', 'played_track_mood.spotify_track_id')
            ->where('played_track_mood.game_id', $game->id)
            ->groupBy('played_tracks.spotify_track_id', 'played_tracks.track_name', 'played_tracks.artist_names',
                'played_tracks.album_name', 'played_tracks.album_image_url', 'played_tracks.duration_ms')
            ->orderBy('play_count', 'desc')
            ->get()
            ->map(function ($track) {
                $track->artists_string = implode(', ', $track->artist_names);

                return $track;
            });

        // Calculate statistics
        $firstPlayed = $tracks->min('first_played');
        $lastPlayed = $tracks->max('last_played');

        $stats = [
            'total_plays' => $tracks->sum('play_count'),
            'unique_tracks' => $tracks->count(),
            'total_duration_ms' => $tracks->sum(function ($track) {
                return $track->duration_ms * $track->play_count;
            }),
            'first_played' => $firstPlayed ? Carbon::parse($firstPlayed) : null,
            'last_played' => $lastPlayed ? Carbon::parse($lastPlayed) : null,
        ];

        // Get moods for tracks
        $trackIds = $tracks->pluck('spotify_track_id')->toArray();
        $trackMoods = PlayedTrack::getMoodsForTracks($trackIds);

        $tracks = $tracks->map(function ($track) use ($trackMoods) {
            $track->moods = $trackMoods[$track->spotify_track_id] ?? [];

            return $track;
        });

        return view('games.show', compact('game', 'tracks', 'stats'));
    }
}
