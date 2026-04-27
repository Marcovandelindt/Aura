<?php

namespace App\Http\Controllers;

use App\Models\LastfmScrobble;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class LastfmTrackController extends Controller
{
    public function show(string $artist, string $track): View
    {
        $artist = urldecode($artist);
        $trackName = urldecode($track);

        $scrobbleQuery = LastfmScrobble::where('track_name', $trackName)
            ->where('artist_name', $artist);

        if ($scrobbleQuery->count() === 0) {
            abort(404, 'Track not found');
        }

        $first = $scrobbleQuery->first();

        $trackInfo = (object) [
            'spotify_track_id' => null,
            'track_name' => $first->track_name,
            'artist_names' => [$first->artist_name],
            'artists_string' => $first->artist_name,
            'album_name' => $first->album_name,
            'album_image_url' => $first->album_image_url,
            'duration_ms' => $first->duration_ms,
            'formatted_duration' => $first->formatted_duration,
            'popularity' => null,
            'spotify_uri' => null,
            'genres' => null,
            'moods' => [],
        ];

        $playedTracks = LastfmScrobble::where('track_name', $trackName)
            ->where('artist_name', $artist)
            ->orderByDesc('played_at')
            ->paginate(20);

        foreach ($playedTracks as $play) {
            $play->source = 'lastfm';
            $play->contexts = null;
        }

        $stats = [
            'total_plays' => $scrobbleQuery->count(),
            'spotify_plays' => 0,
            'lastfm_plays' => $scrobbleQuery->count(),
            'first_played' => ($min = $scrobbleQuery->min('played_at')) ? Carbon::parse($min) : null,
            'last_played' => ($max = $scrobbleQuery->max('played_at')) ? Carbon::parse($max) : null,
            'plays_today' => 0,
            'plays_this_week' => 0,
            'plays_this_month' => 0,
        ];

        $track = $trackInfo;

        return view('tracks.show', compact('track', 'playedTracks', 'stats'));
    }
}
