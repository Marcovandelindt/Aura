<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TrackController extends Controller
{
    public function show(string $spotifyTrackId)
    {
        $trackModel = Track::with(['artists', 'album'])
            ->where('spotify_track_id', $spotifyTrackId)
            ->first();

        if (! $trackModel) {
            abort(404, 'Track not found');
        }

        $track = (object) [
            'spotify_track_id' => $trackModel->spotify_track_id,
            'track_name' => $trackModel->title,
            'artist_names' => $trackModel->artists->pluck('name')->all(),
            'artists_string' => $trackModel->artists_string,
            'album_name' => $trackModel->album?->name,
            'album_image_url' => $trackModel->album?->image_url,
            'duration_ms' => $trackModel->duration_ms,
            'formatted_duration' => $trackModel->formatted_duration,
            'popularity' => $trackModel->popularity,
            'spotify_uri' => $trackModel->spotify_uri,
            'genres' => null,
            'moods' => [],
        ];

        $playedTracks = DB::table('plays')
            ->where('plays.track_id', $trackModel->id)
            ->select('plays.played_at', 'plays.source', 'plays.context as contexts')
            ->orderByDesc('plays.played_at')
            ->paginate(20);

        foreach ($playedTracks as $play) {
            $play->played_at = Carbon::parse($play->played_at);
            $play->contexts = $play->contexts ? json_decode($play->contexts, true) : null;
        }

        $statsRow = DB::table('plays')
            ->where('track_id', $trackModel->id)
            ->selectRaw('COUNT(*) as total_plays')
            ->selectRaw('MIN(played_at) as first_played')
            ->selectRaw('MAX(played_at) as last_played')
            ->first();

        $spotifyPlays = DB::table('plays')->where('track_id', $trackModel->id)->where('source', 'spotify')->count();
        $lastfmPlays = DB::table('plays')->where('track_id', $trackModel->id)->where('source', 'lastfm')->count();

        $stats = [
            'total_plays' => $statsRow->total_plays ?? 0,
            'spotify_plays' => $spotifyPlays,
            'lastfm_plays' => $lastfmPlays,
            'first_played' => $statsRow->first_played ? Carbon::parse($statsRow->first_played) : null,
            'last_played' => $statsRow->last_played ? Carbon::parse($statsRow->last_played) : null,
            'plays_today' => DB::table('plays')->where('track_id', $trackModel->id)->whereDate('played_at', Carbon::today())->count(),
            'plays_this_week' => DB::table('plays')->where('track_id', $trackModel->id)->whereBetween('played_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
            'plays_this_month' => DB::table('plays')->where('track_id', $trackModel->id)->whereMonth('played_at', Carbon::now()->month)->whereYear('played_at', Carbon::now()->year)->count(),
        ];

        return view('tracks.show', compact('track', 'playedTracks', 'stats'));
    }
}
