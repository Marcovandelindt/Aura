<?php

namespace App\Http\Controllers\Gaming;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
        $tracks = DB::table('track_mood')
            ->join('tracks', 'tracks.id', '=', 'track_mood.track_id')
            ->leftJoin('albums', 'albums.id', '=', 'tracks.album_id')
            ->leftJoin('track_artists', function ($join) {
                $join->on('track_artists.track_id', '=', 'tracks.id')
                    ->where('track_artists.is_primary', true);
            })
            ->leftJoin('artists', 'artists.id', '=', 'track_artists.artist_id')
            ->leftJoin('plays', 'plays.track_id', '=', 'tracks.id')
            ->where('track_mood.game_id', $game->id)
            ->select(
                'tracks.id',
                'tracks.spotify_track_id',
                'tracks.title as track_name',
                'tracks.duration_ms',
                'albums.name as album_name',
                'albums.image_url as album_image_url',
                DB::raw("COALESCE(artists.name, '') as artists_string"),
            )
            ->selectRaw('COUNT(plays.id) as play_count')
            ->selectRaw('MAX(plays.played_at) as last_played')
            ->selectRaw('MIN(plays.played_at) as first_played')
            ->groupBy('tracks.id', 'tracks.spotify_track_id', 'tracks.title', 'tracks.duration_ms', 'albums.name', 'albums.image_url', 'artists.name')
            ->orderByDesc('play_count')
            ->get();

        $trackIds = $tracks->pluck('id')->all();

        $moodsByTrack = DB::table('track_mood')
            ->join('moods', 'moods.id', '=', 'track_mood.mood_id')
            ->whereIn('track_mood.track_id', $trackIds)
            ->where('moods.is_active', true)
            ->select('track_mood.track_id', 'moods.id', 'moods.name', 'moods.color', 'moods.icon')
            ->get()
            ->groupBy('track_id');

        $tracks = $tracks->map(function ($track) use ($moodsByTrack) {
            $track->moods = ($moodsByTrack->get($track->id) ?? collect())
                ->map(fn ($m) => ['id' => $m->id, 'name' => $m->name, 'color' => $m->color, 'icon' => $m->icon])
                ->all();

            return $track;
        });

        $stats = [
            'total_plays' => $tracks->sum('play_count'),
            'unique_tracks' => $tracks->count(),
            'total_duration_ms' => $tracks->sum(fn ($t) => ($t->duration_ms ?? 0) * $t->play_count),
            'first_played' => $tracks->min('first_played') ? Carbon::parse($tracks->min('first_played')) : null,
            'last_played' => $tracks->max('last_played') ? Carbon::parse($tracks->max('last_played')) : null,
        ];

        return view('games.show', compact('game', 'tracks', 'stats'));
    }
}
