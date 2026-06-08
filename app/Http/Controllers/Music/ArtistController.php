<?php

namespace App\Http\Controllers\Music;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ArtistController extends Controller
{
    public function show(string $artistName)
    {
        $artistName = urldecode($artistName);

        $artist = Artist::whereRaw('LOWER(name) = ?', [mb_strtolower($artistName)])->first();

        if (! $artist) {
            abort(404, 'Artist not found');
        }

        $uniqueTracks = DB::table('tracks')
            ->join('track_artists', 'track_artists.track_id', '=', 'tracks.id')
            ->leftJoin('albums', 'albums.id', '=', 'tracks.album_id')
            ->leftJoin('plays', 'plays.track_id', '=', 'tracks.id')
            ->where('track_artists.artist_id', $artist->id)
            ->select(
                'tracks.id',
                'tracks.title as track_name',
                'tracks.spotify_track_id',
                'albums.name as album_name',
                'albums.image_url as album_image_url',
            )
            ->selectRaw('COUNT(plays.id) as play_count')
            ->selectRaw('MAX(plays.played_at) as last_played')
            ->groupBy('tracks.id', 'tracks.title', 'tracks.spotify_track_id', 'albums.name', 'albums.image_url')
            ->orderByDesc('play_count')
            ->paginate(20);

        foreach ($uniqueTracks as $track) {
            $track->moods = [];
        }

        $allPlays = DB::table('plays')
            ->join('tracks', 'tracks.id', '=', 'plays.track_id')
            ->join('track_artists', 'track_artists.track_id', '=', 'tracks.id')
            ->leftJoin('albums', 'albums.id', '=', 'tracks.album_id')
            ->where('track_artists.artist_id', $artist->id)
            ->select(
                'plays.played_at',
                'plays.source',
                'tracks.title as track_name',
                'tracks.spotify_track_id',
                'albums.name as album_name',
                'albums.image_url as album_image_url',
            )
            ->orderByDesc('plays.played_at')
            ->paginate(30, ['*'], 'plays_page');

        foreach ($allPlays as $play) {
            $play->played_at = Carbon::parse($play->played_at);
        }

        $statsRow = DB::table('plays')
            ->join('track_artists', 'track_artists.track_id', '=', 'plays.track_id')
            ->join('tracks', 'tracks.id', '=', 'plays.track_id')
            ->where('track_artists.artist_id', $artist->id)
            ->selectRaw('COUNT(plays.id) as total_plays')
            ->selectRaw('SUM(tracks.duration_ms) as total_duration_ms')
            ->selectRaw('MIN(plays.played_at) as first_played')
            ->selectRaw('MAX(plays.played_at) as last_played')
            ->first();

        $weekPlays = DB::table('plays')
            ->join('track_artists', 'track_artists.track_id', '=', 'plays.track_id')
            ->where('track_artists.artist_id', $artist->id)
            ->whereBetween('plays.played_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count();

        $monthPlays = DB::table('plays')
            ->join('track_artists', 'track_artists.track_id', '=', 'plays.track_id')
            ->where('track_artists.artist_id', $artist->id)
            ->whereMonth('plays.played_at', Carbon::now()->month)
            ->whereYear('plays.played_at', Carbon::now()->year)
            ->count();

        $stats = [
            'total_plays' => $statsRow->total_plays ?? 0,
            'unique_tracks' => $uniqueTracks->total(),
            'first_played' => $statsRow->first_played ? Carbon::parse($statsRow->first_played) : null,
            'last_played' => $statsRow->last_played ? Carbon::parse($statsRow->last_played) : null,
            'total_duration_ms' => $statsRow->total_duration_ms ?? 0,
            'plays_this_week' => $weekPlays,
            'plays_this_month' => $monthPlays,
            'spotify_plays' => 0,
            'lastfm_plays' => 0,
            'plays_today' => 0,
        ];

        $topTrack = $uniqueTracks->first();

        return view('artists.show', compact('artistName', 'uniqueTracks', 'allPlays', 'stats', 'topTrack'));
    }
}
