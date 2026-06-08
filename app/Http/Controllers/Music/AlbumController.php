<?php

namespace App\Http\Controllers\Music;

use App\Http\Controllers\Controller;
use App\Models\Album;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AlbumController extends Controller
{
    public function show(string $albumName)
    {
        $albumName = urldecode($albumName);

        $album = Album::whereRaw('LOWER(name) = ?', [mb_strtolower($albumName)])->first();

        if (! $album) {
            abort(404, 'Album not found');
        }

        $albumTracks = DB::table('tracks')
            ->leftJoin('plays', 'plays.track_id', '=', 'tracks.id')
            ->where('tracks.album_id', $album->id)
            ->select(
                'tracks.id',
                'tracks.title as track_name',
                'tracks.spotify_track_id',
                DB::raw("'{$album->name}' as album_name"),
                DB::raw("'{$album->image_url}' as album_image_url"),
                'tracks.duration_ms',
            )
            ->selectRaw('COUNT(plays.id) as play_count')
            ->selectRaw('MAX(plays.played_at) as last_played')
            ->groupBy('tracks.id', 'tracks.title', 'tracks.spotify_track_id', 'tracks.duration_ms')
            ->orderByDesc('play_count')
            ->paginate(20);

        $trackIds = $albumTracks->pluck('id')->all();

        $artistsByTrack = DB::table('track_artists')
            ->join('artists', 'artists.id', '=', 'track_artists.artist_id')
            ->whereIn('track_artists.track_id', $trackIds)
            ->select('track_artists.track_id', 'artists.name')
            ->orderByDesc('track_artists.is_primary')
            ->get()
            ->groupBy('track_id')
            ->map(fn ($items) => $items->pluck('name')->all());

        foreach ($albumTracks as $track) {
            $track->moods = [];
            $track->artist_names = $artistsByTrack->get($track->id, []);
        }

        $allPlays = DB::table('plays')
            ->join('tracks', 'tracks.id', '=', 'plays.track_id')
            ->where('tracks.album_id', $album->id)
            ->select(
                'plays.played_at',
                'plays.source',
                'tracks.id as track_id',
                'tracks.title as track_name',
                'tracks.spotify_track_id',
                DB::raw("'{$album->name}' as album_name"),
                DB::raw("'{$album->image_url}' as album_image_url"),
                'tracks.duration_ms',
            )
            ->orderByDesc('plays.played_at')
            ->paginate(30, ['*'], 'plays');

        $playTrackIds = $allPlays->pluck('track_id')->unique()->all();

        $artistsByPlayTrack = DB::table('track_artists')
            ->join('artists', 'artists.id', '=', 'track_artists.artist_id')
            ->whereIn('track_artists.track_id', $playTrackIds)
            ->select('track_artists.track_id', 'artists.name')
            ->orderByDesc('track_artists.is_primary')
            ->get()
            ->groupBy('track_id')
            ->map(fn ($items) => $items->pluck('name')->all());

        foreach ($allPlays as $play) {
            $play->played_at = Carbon::parse($play->played_at);
            $play->artist_names = $artistsByPlayTrack->get($play->track_id, []);
        }

        $statsRow = DB::table('plays')
            ->join('tracks', 'tracks.id', '=', 'plays.track_id')
            ->where('tracks.album_id', $album->id)
            ->selectRaw('COUNT(plays.id) as total_plays')
            ->selectRaw('COUNT(DISTINCT tracks.id) as unique_tracks')
            ->selectRaw('MIN(plays.played_at) as first_played')
            ->selectRaw('MAX(plays.played_at) as last_played')
            ->selectRaw('SUM(tracks.duration_ms) as total_duration_ms')
            ->first();

        $stats = [
            'total_plays' => $statsRow->total_plays ?? 0,
            'unique_tracks' => $statsRow->unique_tracks ?? 0,
            'first_played' => $statsRow->first_played ? Carbon::parse($statsRow->first_played) : null,
            'last_played' => $statsRow->last_played ? Carbon::parse($statsRow->last_played) : null,
            'plays_today' => DB::table('plays')->join('tracks', 'tracks.id', '=', 'plays.track_id')->where('tracks.album_id', $album->id)->whereDate('plays.played_at', Carbon::today())->count(),
            'plays_this_week' => DB::table('plays')->join('tracks', 'tracks.id', '=', 'plays.track_id')->where('tracks.album_id', $album->id)->whereBetween('plays.played_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
            'plays_this_month' => DB::table('plays')->join('tracks', 'tracks.id', '=', 'plays.track_id')->where('tracks.album_id', $album->id)->whereMonth('plays.played_at', Carbon::now()->month)->whereYear('plays.played_at', Carbon::now()->year)->count(),
            'total_duration_ms' => $statsRow->total_duration_ms ?? 0,
        ];

        $topTrack = $albumTracks->first();

        $albumArtists = DB::table('artists')
            ->join('track_artists', 'track_artists.artist_id', '=', 'artists.id')
            ->join('tracks', 'tracks.id', '=', 'track_artists.track_id')
            ->where('tracks.album_id', $album->id)
            ->distinct()
            ->pluck('artists.name');

        return view('albums.show', compact('albumName', 'albumTracks', 'allPlays', 'stats', 'topTrack', 'albumArtists'));
    }
}
