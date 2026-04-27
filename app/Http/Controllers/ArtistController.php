<?php

namespace App\Http\Controllers;

use App\Models\LastfmScrobble;
use App\Models\PlayedTrack;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ArtistController extends Controller
{
    public function show(string $artistName)
    {
        $artistName = urldecode($artistName);
        $firstSpotifyPlay = PlayedTrack::min('played_at');

        $spotifyCount = PlayedTrack::whereJsonContains('artist_names', $artistName)->count();

        $lastfmQuery = LastfmScrobble::where('artist_name', $artistName);
        if ($firstSpotifyPlay) {
            $lastfmQuery->where('played_at', '<', $firstSpotifyPlay);
        }
        $lastfmCount = $lastfmQuery->count();

        if ($spotifyCount === 0 && $lastfmCount === 0) {
            abort(404, 'Artist not found');
        }

        // Combined unique tracks (merged by track_name)
        $spotifyTracksQuery = DB::table('played_tracks')
            ->whereJsonContains('artist_names', $artistName)
            ->select('track_name', 'album_name', 'album_image_url', 'spotify_track_id')
            ->selectRaw('COUNT(*) as play_count')
            ->selectRaw('MAX(played_at) as last_played')
            ->groupBy('track_name', 'album_name', 'album_image_url', 'spotify_track_id');

        $lastfmTracksQuery = DB::table('lastfm_scrobbles')
            ->where('artist_name', $artistName)
            ->select('track_name', 'album_name', 'album_image_url', DB::raw('NULL as spotify_track_id'))
            ->selectRaw('COUNT(*) as play_count')
            ->selectRaw('MAX(played_at) as last_played')
            ->groupBy('track_name', 'album_name', 'album_image_url');

        if ($firstSpotifyPlay) {
            $lastfmTracksQuery->where('played_at', '<', $firstSpotifyPlay);
        }

        $uniqueTracks = DB::query()
            ->fromSub($spotifyTracksQuery->union($lastfmTracksQuery), 'combined')
            ->select('track_name')
            ->selectRaw('MAX(album_name) as album_name')
            ->selectRaw('MAX(album_image_url) as album_image_url')
            ->selectRaw('MAX(spotify_track_id) as spotify_track_id')
            ->selectRaw('SUM(play_count) as play_count')
            ->selectRaw('MAX(last_played) as last_played')
            ->groupBy('track_name')
            ->orderByDesc('play_count')
            ->paginate(20);

        // Load moods for tracks that have a spotify_track_id
        $trackIds = $uniqueTracks->pluck('spotify_track_id')->filter()->toArray();
        $trackMoods = PlayedTrack::getMoodsForTracks($trackIds);
        foreach ($uniqueTracks as $track) {
            $track->moods = $trackMoods[$track->spotify_track_id] ?? [];
        }

        // Combined play timeline
        $spotifyPlaysQuery = DB::table('played_tracks')
            ->whereJsonContains('artist_names', $artistName)
            ->select('track_name', 'album_name', 'album_image_url', 'played_at', 'spotify_track_id', DB::raw("'spotify' as source"));

        $lastfmPlaysQuery = DB::table('lastfm_scrobbles')
            ->where('artist_name', $artistName)
            ->select('track_name', 'album_name', 'album_image_url', 'played_at', DB::raw('NULL as spotify_track_id'), DB::raw("'lastfm' as source"));

        if ($firstSpotifyPlay) {
            $lastfmPlaysQuery->where('played_at', '<', $firstSpotifyPlay);
        }

        $allPlays = DB::query()
            ->fromSub($spotifyPlaysQuery->union($lastfmPlaysQuery), 'plays')
            ->orderByDesc('played_at')
            ->paginate(30, ['*'], 'plays_page');

        foreach ($allPlays as $play) {
            $play->played_at = Carbon::parse($play->played_at);
        }

        // Stats (combined)
        $spotifyFirst = PlayedTrack::whereJsonContains('artist_names', $artistName)->min('played_at');
        $lastfmFirst = (clone $lastfmQuery)->min('played_at');
        $spotifyLast = PlayedTrack::whereJsonContains('artist_names', $artistName)->max('played_at');

        $firstPlayed = $spotifyFirst && $lastfmFirst
            ? (Carbon::parse($lastfmFirst)->lt(Carbon::parse($spotifyFirst)) ? Carbon::parse($lastfmFirst) : Carbon::parse($spotifyFirst))
            : Carbon::parse($spotifyFirst ?? $lastfmFirst);

        $lastPlayed = $spotifyLast ? Carbon::parse($spotifyLast) : null;

        $spotifyDuration = PlayedTrack::whereJsonContains('artist_names', $artistName)->sum('duration_ms');
        $lastfmDuration = (clone $lastfmQuery)->sum('duration_ms');

        $stats = [
            'total_plays' => $spotifyCount + $lastfmCount,
            'spotify_plays' => $spotifyCount,
            'lastfm_plays' => $lastfmCount,
            'unique_tracks' => $uniqueTracks->total(),
            'first_played' => $firstPlayed,
            'last_played' => $lastPlayed,
            'plays_today' => PlayedTrack::whereJsonContains('artist_names', $artistName)->whereDate('played_at', Carbon::today())->count(),
            'plays_this_week' => PlayedTrack::whereJsonContains('artist_names', $artistName)->whereBetween('played_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
            'plays_this_month' => PlayedTrack::whereJsonContains('artist_names', $artistName)->whereMonth('played_at', Carbon::now()->month)->whereYear('played_at', Carbon::now()->year)->count(),
            'total_duration_ms' => $spotifyDuration + $lastfmDuration,
        ];

        // Top track (by combined play count)
        $topTrack = $uniqueTracks->first();

        return view('artists.show', compact('artistName', 'uniqueTracks', 'allPlays', 'stats', 'topTrack'));
    }
}
