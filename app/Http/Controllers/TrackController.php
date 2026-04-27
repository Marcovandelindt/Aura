<?php

namespace App\Http\Controllers;

use App\Models\LastfmScrobble;
use App\Models\PlayedTrack;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TrackController extends Controller
{
    public function show(string $spotifyTrackId)
    {
        $firstPlayedTrack = PlayedTrack::where('spotify_track_id', $spotifyTrackId)->first();

        if (! $firstPlayedTrack) {
            abort(404, 'Track not found');
        }

        $trackMoods = PlayedTrack::getMoodsForTrack($spotifyTrackId);

        $track = (object) [
            'spotify_track_id' => $firstPlayedTrack->spotify_track_id,
            'track_name' => $firstPlayedTrack->track_name,
            'artist_names' => $firstPlayedTrack->artist_names,
            'artists_string' => implode(', ', $firstPlayedTrack->artist_names),
            'album_name' => $firstPlayedTrack->album_name,
            'album_image_url' => $firstPlayedTrack->album_image_url,
            'duration_ms' => $firstPlayedTrack->duration_ms,
            'formatted_duration' => sprintf('%d:%02d',
                floor($firstPlayedTrack->duration_ms / 1000 / 60),
                floor(($firstPlayedTrack->duration_ms / 1000) % 60)
            ),
            'popularity' => $firstPlayedTrack->popularity,
            'spotify_uri' => $firstPlayedTrack->spotify_uri,
            'genres' => null,
            'moods' => $trackMoods->map(fn ($mood) => [
                'id' => $mood->id,
                'name' => $mood->name,
                'color' => $mood->color,
                'icon' => $mood->icon,
            ])->toArray(),
        ];

        // Look up matching Last.fm plays (before Spotify tracking started)
        $firstSpotifyPlay = PlayedTrack::min('played_at');
        $primaryArtist = $firstPlayedTrack->artist_names[0] ?? '';

        $lastfmQuery = LastfmScrobble::where('track_name', $firstPlayedTrack->track_name)
            ->where('artist_name', $primaryArtist);
        if ($firstSpotifyPlay) {
            $lastfmQuery->where('played_at', '<', $firstSpotifyPlay);
        }

        $lastfmCount = $lastfmQuery->count();
        $lastfmFirstPlayed = $lastfmQuery->min('played_at');

        $spotifyCount = PlayedTrack::where('spotify_track_id', $spotifyTrackId)->count();
        $spotifyFirstPlayed = PlayedTrack::where('spotify_track_id', $spotifyTrackId)->min('played_at');
        $spotifyLastPlayed = PlayedTrack::where('spotify_track_id', $spotifyTrackId)->max('played_at');

        $firstPlayed = $lastfmFirstPlayed && Carbon::parse($lastfmFirstPlayed)->lt(Carbon::parse($spotifyFirstPlayed))
            ? Carbon::parse($lastfmFirstPlayed)
            : Carbon::parse($spotifyFirstPlayed);

        // Combined play timeline
        $spotifyPlaysQuery = DB::table('played_tracks')
            ->where('spotify_track_id', $spotifyTrackId)
            ->select('played_at', DB::raw("'spotify' as source"), 'contexts');

        $lastfmPlaysQuery = DB::table('lastfm_scrobbles')
            ->where('track_name', $firstPlayedTrack->track_name)
            ->where('artist_name', $primaryArtist)
            ->select('played_at', DB::raw("'lastfm' as source"), DB::raw('NULL as contexts'));

        if ($firstSpotifyPlay) {
            $lastfmPlaysQuery->where('played_at', '<', $firstSpotifyPlay);
        }

        $playedTracks = DB::query()
            ->fromSub($spotifyPlaysQuery->union($lastfmPlaysQuery), 'history')
            ->orderByDesc('played_at')
            ->paginate(20);

        foreach ($playedTracks as $play) {
            $play->played_at = Carbon::parse($play->played_at);
            $play->contexts = $play->contexts ? json_decode($play->contexts, true) : null;
        }

        $stats = [
            'total_plays' => $spotifyCount + $lastfmCount,
            'spotify_plays' => $spotifyCount,
            'lastfm_plays' => $lastfmCount,
            'first_played' => $firstPlayed,
            'last_played' => $spotifyLastPlayed ? Carbon::parse($spotifyLastPlayed) : null,
            'plays_today' => PlayedTrack::where('spotify_track_id', $spotifyTrackId)->whereDate('played_at', Carbon::today())->count(),
            'plays_this_week' => PlayedTrack::where('spotify_track_id', $spotifyTrackId)->whereBetween('played_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
            'plays_this_month' => PlayedTrack::where('spotify_track_id', $spotifyTrackId)->whereMonth('played_at', Carbon::now()->month)->whereYear('played_at', Carbon::now()->year)->count(),
        ];

        return view('tracks.show', compact('track', 'playedTracks', 'stats'));
    }
}
