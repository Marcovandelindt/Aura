<?php

namespace App\Http\Controllers\Music;

use App\Http\Controllers\Controller;
use App\Models\Track;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LastfmTrackController extends Controller
{
    public function show(string $artist, string $track): View
    {
        $artistName = urldecode($artist);
        $trackName = urldecode($track);

        $trackModel = Track::query()
            ->whereRaw('LOWER(title) = ?', [mb_strtolower($trackName)])
            ->whereHas('artists', fn ($q) => $q->whereRaw('LOWER(name) = ?', [mb_strtolower($artistName)]))
            ->with(['artists', 'album'])
            ->first();

        if (! $trackModel) {
            abort(404, 'Track not found');
        }

        $trackInfo = (object) [
            'spotify_track_id' => $trackModel->spotify_track_id,
            'track_name' => $trackModel->title,
            'artist_names' => $trackModel->artists->pluck('name')->all(),
            'artists_string' => $trackModel->artists_string,
            'album_name' => $trackModel->album?->name,
            'album_image_url' => $trackModel->album?->image_url,
            'duration_ms' => $trackModel->duration_ms,
            'formatted_duration' => $trackModel->formatted_duration,
            'popularity' => null,
            'spotify_uri' => null,
            'genres' => null,
            'moods' => [],
        ];

        $playedTracks = DB::table('plays')
            ->where('track_id', $trackModel->id)
            ->select('plays.played_at', 'plays.source', 'plays.context as contexts')
            ->orderByDesc('played_at')
            ->paginate(20);

        foreach ($playedTracks as $play) {
            $play->played_at = Carbon::parse($play->played_at);
            $play->source = $play->source ?? 'lastfm';
            $play->contexts = null;
        }

        $statsRow = DB::table('plays')
            ->where('track_id', $trackModel->id)
            ->selectRaw('COUNT(*) as total_plays')
            ->selectRaw('SUM(source = "lastfm") as lastfm_plays')
            ->selectRaw('MIN(played_at) as first_played')
            ->selectRaw('MAX(played_at) as last_played')
            ->first();

        $stats = [
            'total_plays' => $statsRow->total_plays ?? 0,
            'spotify_plays' => 0,
            'lastfm_plays' => $statsRow->lastfm_plays ?? 0,
            'first_played' => $statsRow->first_played ? Carbon::parse($statsRow->first_played) : null,
            'last_played' => $statsRow->last_played ? Carbon::parse($statsRow->last_played) : null,
            'plays_today' => 0,
            'plays_this_week' => 0,
            'plays_this_month' => 0,
        ];

        $track = $trackInfo;

        return view('tracks.show', compact('track', 'playedTracks', 'stats'));
    }
}
