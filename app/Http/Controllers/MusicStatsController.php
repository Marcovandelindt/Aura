<?php

namespace App\Http\Controllers;

use App\Models\LastfmScrobble;
use App\Models\Mood;
use App\Models\PlayedTrack;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MusicStatsController extends Controller
{
    private ?string $firstSpotifyPlay = null;

    public function index(): View
    {
        $this->firstSpotifyPlay = PlayedTrack::min('played_at');

        $stats = [
            'top_artists' => $this->getTopArtists(),
            'top_tracks' => $this->getTopTracks(),
            'top_albums' => $this->getTopAlbums(),
            'top_moods' => $this->getTopMoods(),
            'listening_stats' => $this->getListeningStats(),
            'top_listening_times' => $this->getTopListeningTimes(),
            'weekday_vs_weekend' => $this->getWeekdayVsWeekendStats(),
            'repeat_ratio' => $this->getRepeatRatio(),
            'binge_sessions' => $this->getBingeSessions(),
            'discovery_rate' => $this->getDiscoveryRate(),
        ];

        return view('music.stats', compact('stats'));
    }

    private function lastfmQuery(): Builder
    {
        $query = LastfmScrobble::query();
        if ($this->firstSpotifyPlay) {
            $query->where('played_at', '<', $this->firstSpotifyPlay);
        }

        return $query;
    }

    private function lastfmDbQuery(): QueryBuilder
    {
        $query = DB::table('lastfm_scrobbles');
        if ($this->firstSpotifyPlay) {
            $query->where('played_at', '<', $this->firstSpotifyPlay);
        }

        return $query;
    }

    private function getTopArtists(int $limit = 10): array
    {
        $artistStats = [];

        $tracks = PlayedTrack::select('artist_names', 'played_at', 'duration_ms', 'spotify_track_id')->cursor();
        foreach ($tracks as $track) {
            foreach ($track->artist_names as $artist) {
                if (! isset($artistStats[$artist])) {
                    $artistStats[$artist] = ['name' => $artist, 'play_count' => 0, 'total_duration_ms' => 0, 'unique_tracks' => [], 'first_played' => null, 'last_played' => null];
                }
                $artistStats[$artist]['play_count']++;
                $artistStats[$artist]['total_duration_ms'] += $track->duration_ms;
                $artistStats[$artist]['unique_tracks'][$track->spotify_track_id] = true;
                if (! $artistStats[$artist]['first_played'] || $track->played_at < $artistStats[$artist]['first_played']) {
                    $artistStats[$artist]['first_played'] = $track->played_at;
                }
                if (! $artistStats[$artist]['last_played'] || $track->played_at > $artistStats[$artist]['last_played']) {
                    $artistStats[$artist]['last_played'] = $track->played_at;
                }
            }
        }

        // Last.fm: aggregate at DB level to avoid loading all rows into memory
        $lastfmArtists = $this->lastfmDbQuery()
            ->select('artist_name')
            ->selectRaw('COUNT(*) as play_count')
            ->selectRaw('SUM(COALESCE(duration_ms, 0)) as total_duration_ms')
            ->selectRaw('COUNT(DISTINCT COALESCE(spotify_track_id, track_name)) as unique_tracks_count')
            ->selectRaw('MIN(played_at) as first_played')
            ->selectRaw('MAX(played_at) as last_played')
            ->groupBy('artist_name')
            ->get();

        foreach ($lastfmArtists as $lfm) {
            $artist = $lfm->artist_name;
            if (! isset($artistStats[$artist])) {
                $artistStats[$artist] = ['name' => $artist, 'play_count' => 0, 'total_duration_ms' => 0, 'unique_tracks' => [], 'lfm_unique_count' => 0, 'first_played' => null, 'last_played' => null];
            }
            $artistStats[$artist]['play_count'] += $lfm->play_count;
            $artistStats[$artist]['total_duration_ms'] += $lfm->total_duration_ms;
            $artistStats[$artist]['lfm_unique_count'] = ($artistStats[$artist]['lfm_unique_count'] ?? 0) + $lfm->unique_tracks_count;
            $lfmFirst = Carbon::parse($lfm->first_played);
            $lfmLast = Carbon::parse($lfm->last_played);
            if (! $artistStats[$artist]['first_played'] || $lfmFirst < $artistStats[$artist]['first_played']) {
                $artistStats[$artist]['first_played'] = $lfmFirst;
            }
            if (! $artistStats[$artist]['last_played'] || $lfmLast > $artistStats[$artist]['last_played']) {
                $artistStats[$artist]['last_played'] = $lfmLast;
            }
        }

        // Apply artist corrections: attribute plays to featured artists that Last.fm missed
        $corrections = DB::table('lastfm_track_corrections')->get();
        foreach ($corrections as $correction) {
            $allArtists = json_decode($correction->all_artist_names, true) ?? [];
            $extraArtists = array_filter($allArtists, fn ($a) => strtolower($a) !== strtolower($correction->artist_name));

            if (empty($extraArtists)) {
                continue;
            }

            $trackStats = $this->lastfmDbQuery()
                ->where('track_name', $correction->track_name)
                ->where('artist_name', $correction->artist_name)
                ->selectRaw('COUNT(*) as play_count, SUM(COALESCE(duration_ms, 0)) as total_duration_ms, MIN(played_at) as first_played, MAX(played_at) as last_played')
                ->first();

            if (! $trackStats || $trackStats->play_count === 0) {
                continue;
            }

            foreach ($extraArtists as $extraArtist) {
                if (! isset($artistStats[$extraArtist])) {
                    $artistStats[$extraArtist] = ['name' => $extraArtist, 'play_count' => 0, 'total_duration_ms' => 0, 'unique_tracks' => [], 'lfm_unique_count' => 0, 'first_played' => null, 'last_played' => null];
                }
                $artistStats[$extraArtist]['play_count'] += $trackStats->play_count;
                $artistStats[$extraArtist]['total_duration_ms'] += $trackStats->total_duration_ms;
                $artistStats[$extraArtist]['lfm_unique_count'] = ($artistStats[$extraArtist]['lfm_unique_count'] ?? 0) + 1;
                $first = Carbon::parse($trackStats->first_played);
                $last = Carbon::parse($trackStats->last_played);
                if (! $artistStats[$extraArtist]['first_played'] || $first < $artistStats[$extraArtist]['first_played']) {
                    $artistStats[$extraArtist]['first_played'] = $first;
                }
                if (! $artistStats[$extraArtist]['last_played'] || $last > $artistStats[$extraArtist]['last_played']) {
                    $artistStats[$extraArtist]['last_played'] = $last;
                }
            }
        }

        foreach ($artistStats as &$stats) {
            $stats['unique_tracks_count'] = count($stats['unique_tracks']) + ($stats['lfm_unique_count'] ?? 0);
            unset($stats['unique_tracks'], $stats['lfm_unique_count']);
        }

        usort($artistStats, fn ($a, $b) => $b['play_count'] <=> $a['play_count']);

        return array_slice($artistStats, 0, $limit);
    }

    private function getTopTracks(int $limit = 10): array
    {
        $tracks = PlayedTrack::select('spotify_track_id', 'track_name', 'artist_names', 'album_name', 'album_image_url', 'duration_ms')
            ->selectRaw('COUNT(*) as play_count')
            ->selectRaw('MAX(played_at) as last_played')
            ->selectRaw('MIN(played_at) as first_played')
            ->groupBy('spotify_track_id', 'track_name', 'artist_names', 'album_name', 'album_image_url', 'duration_ms')
            ->get();

        // Build a lookup by lowercase track_name to match Last.fm plays against Spotify tracks
        $byName = $tracks->keyBy(fn ($t) => strtolower($t->track_name));

        $lastfmTracks = $this->lastfmDbQuery()
            ->select('track_name', 'artist_name')
            ->selectRaw('COUNT(*) as play_count')
            ->selectRaw('MIN(album_name) as album_name')
            ->selectRaw('MIN(album_image_url) as album_image_url')
            ->selectRaw('MIN(duration_ms) as duration_ms')
            ->selectRaw('MIN(played_at) as first_played')
            ->groupBy('track_name', 'artist_name')
            ->get();

        foreach ($lastfmTracks as $lfm) {
            $nameKey = strtolower($lfm->track_name);
            if ($byName->has($nameKey)) {
                $byName[$nameKey]->play_count += $lfm->play_count;
            } else {
                $entry = (object) [
                    'spotify_track_id' => null,
                    'track_name' => $lfm->track_name,
                    'artist_names' => [$lfm->artist_name],
                    'album_name' => $lfm->album_name,
                    'album_image_url' => $lfm->album_image_url,
                    'duration_ms' => $lfm->duration_ms,
                    'play_count' => $lfm->play_count,
                    'first_played' => $lfm->first_played,
                    'last_played' => null,
                ];
                $tracks->push($entry);
                $byName->put($nameKey, $entry);
            }
        }

        $topTracks = $tracks->sortByDesc('play_count')->take($limit)->values()
            ->map(function ($track) {
                $track->artists_string = implode(', ', (array) $track->artist_names);

                return $track;
            });

        $trackIds = $topTracks->filter(fn ($t) => $t->spotify_track_id)->pluck('spotify_track_id')->toArray();
        $trackMoods = PlayedTrack::getMoodsForTracks($trackIds);

        return $topTracks->map(function ($track) use ($trackMoods) {
            $trackId = $track->spotify_track_id ?? null;

            // Normalize to array — Eloquent models use toArray(), stdClass uses json round-trip
            $data = method_exists($track, 'toArray')
                ? $track->toArray()
                : json_decode(json_encode($track), true);

            $data['moods'] = $trackId ? ($trackMoods[$trackId] ?? []) : [];

            return $data;
        })->all();
    }

    private function getTopAlbums(int $limit = 10): array
    {
        $albumMap = [];

        $spotifyAlbums = PlayedTrack::select('album_name', 'artist_names', 'album_image_url')
            ->selectRaw('COUNT(*) as play_count')
            ->selectRaw('COUNT(DISTINCT spotify_track_id) as unique_tracks_count')
            ->groupBy('album_name', 'artist_names', 'album_image_url')
            ->get();

        foreach ($spotifyAlbums as $album) {
            $key = strtolower(trim($album->album_name));
            $albumMap[$key] = [
                'album_name' => $album->album_name,
                'artists_string' => implode(', ', array_unique((array) $album->artist_names)),
                'album_image_url' => $album->album_image_url,
                'play_count' => $album->play_count,
                'unique_tracks_count' => $album->unique_tracks_count,
            ];
        }

        $lastfmAlbums = $this->lastfmQuery()
            ->whereNotNull('album_name')
            ->select('album_name', 'artist_name', 'album_image_url')
            ->selectRaw('COUNT(*) as play_count')
            ->selectRaw('COUNT(DISTINCT track_name) as unique_tracks_count')
            ->groupBy('album_name', 'artist_name', 'album_image_url')
            ->get();

        foreach ($lastfmAlbums as $album) {
            $key = strtolower(trim($album->album_name));
            if (isset($albumMap[$key])) {
                $albumMap[$key]['play_count'] += $album->play_count;
                $albumMap[$key]['unique_tracks_count'] = max($albumMap[$key]['unique_tracks_count'], $album->unique_tracks_count);
                if (! $albumMap[$key]['album_image_url'] && $album->album_image_url) {
                    $albumMap[$key]['album_image_url'] = $album->album_image_url;
                }
            } else {
                $albumMap[$key] = [
                    'album_name' => $album->album_name,
                    'artists_string' => $album->artist_name,
                    'album_image_url' => $album->album_image_url,
                    'play_count' => $album->play_count,
                    'unique_tracks_count' => $album->unique_tracks_count,
                ];
            }
        }

        usort($albumMap, fn ($a, $b) => $b['play_count'] <=> $a['play_count']);

        return array_slice($albumMap, 0, $limit);
    }

    private function getTopMoods(int $limit = 10): array
    {
        return Mood::query()
            ->select('moods.id', 'moods.name', 'moods.color', 'moods.icon')
            ->selectRaw('COUNT(played_tracks.id) as play_count')
            ->selectRaw('COUNT(DISTINCT played_track_mood.spotify_track_id) as track_count')
            ->join('played_track_mood', 'moods.id', '=', 'played_track_mood.mood_id')
            ->join('played_tracks', 'played_track_mood.spotify_track_id', '=', 'played_tracks.spotify_track_id')
            ->where('moods.is_active', true)
            ->groupBy('moods.id', 'moods.name', 'moods.color', 'moods.icon')
            ->orderByDesc('play_count')
            ->limit($limit)
            ->get()
            ->map(fn ($mood) => [
                'id' => $mood->id,
                'name' => $mood->name,
                'color' => $mood->color,
                'icon' => $mood->icon,
                'play_count' => $mood->play_count,
                'track_count' => $mood->track_count,
            ])
            ->toArray();
    }

    private function getListeningStats(): array
    {
        $spotifyTotal = PlayedTrack::count();
        $spotifyDuration = PlayedTrack::sum('duration_ms');
        $firstSpotifyTrack = PlayedTrack::orderBy('played_at')->first();
        $lastSpotifyTrack = PlayedTrack::orderBy('played_at', 'desc')->first();

        $lastfmTotal = $this->lastfmQuery()->count();
        $lastfmDuration = (int) $this->lastfmQuery()->sum('duration_ms');
        $lastfmFirstTrack = $this->lastfmQuery()->orderBy('played_at')->first();

        $spotifyUnique = (int) PlayedTrack::selectRaw('COUNT(DISTINCT spotify_track_id) as cnt')->value('cnt');

        $lastfmUniqueWithId = (int) $this->lastfmQuery()
            ->whereNotNull('spotify_track_id')
            ->whereNotIn('spotify_track_id', fn ($q) => $q->select('spotify_track_id')->distinct()->from('played_tracks'))
            ->selectRaw('COUNT(DISTINCT spotify_track_id) as cnt')
            ->value('cnt');

        $lastfmUniqueWithoutId = (int) $this->lastfmQuery()
            ->whereNull('spotify_track_id')
            ->selectRaw("COUNT(DISTINCT CONCAT(track_name, '|', artist_name)) as cnt")
            ->value('cnt');

        $totalTracks = $spotifyTotal + $lastfmTotal;
        $uniqueTracks = $spotifyUnique + $lastfmUniqueWithId + $lastfmUniqueWithoutId;
        $totalDuration = $spotifyDuration + $lastfmDuration;

        if ($firstSpotifyTrack && $lastfmFirstTrack) {
            $firstTrack = $lastfmFirstTrack->played_at < $firstSpotifyTrack->played_at ? $lastfmFirstTrack : $firstSpotifyTrack;
        } else {
            $firstTrack = $firstSpotifyTrack ?? $lastfmFirstTrack;
        }

        $todayTracks = PlayedTrack::whereDate('played_at', Carbon::today())->count()
            + $this->lastfmQuery()->whereDate('played_at', Carbon::today())->count();

        $weekTracks = PlayedTrack::whereBetween('played_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count()
            + $this->lastfmQuery()->whereBetween('played_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();

        $monthTracks = PlayedTrack::whereMonth('played_at', Carbon::now()->month)->whereYear('played_at', Carbon::now()->year)->count()
            + $this->lastfmQuery()->whereMonth('played_at', Carbon::now()->month)->whereYear('played_at', Carbon::now()->year)->count();

        return [
            'total_tracks' => $totalTracks,
            'unique_tracks' => $uniqueTracks,
            'total_duration_ms' => $totalDuration,
            'total_duration_hours' => round($totalDuration / 1000 / 60 / 60, 1),
            'first_track_date' => $firstTrack?->played_at,
            'last_track_date' => $lastSpotifyTrack?->played_at,
            'tracks_today' => $todayTracks,
            'tracks_this_week' => $weekTracks,
            'tracks_this_month' => $monthTracks,
            'average_per_day' => $firstTrack ? round($totalTracks / max(1, $firstTrack->played_at->diffInDays(Carbon::now())), 1) : 0,
        ];
    }

    private function getTopListeningTimes(): array
    {
        $spotifyQuery = DB::table('played_tracks')
            ->selectRaw('HOUR(DATE_ADD(played_at, INTERVAL 1 HOUR)) as hour');

        $lastfmQuery = $this->lastfmDbQuery()
            ->selectRaw('HOUR(DATE_ADD(played_at, INTERVAL 1 HOUR)) as hour');

        $tracks = DB::query()
            ->fromSub($spotifyQuery->unionAll($lastfmQuery), 'combined')
            ->selectRaw('hour, COUNT(*) as play_count')
            ->groupBy('hour')
            ->orderByDesc('play_count')
            ->limit(3)
            ->get();

        return $tracks->map(function ($track) {
            $hour = (int) ($track->hour ?? 0);

            return [
                'hour' => $hour,
                'time_label' => $this->getTimeLabel($hour),
                'time_range' => $this->getTimeRange($hour),
                'play_count' => $track->play_count,
                'emoji' => $this->getTimeEmoji($hour),
                'description' => $this->getTimeDescription($hour),
            ];
        })->toArray();
    }

    private function getWeekdayVsWeekendStats(): array
    {
        $spotifyWeekday = DB::table('played_tracks')->whereRaw('WEEKDAY(played_at) BETWEEN 0 AND 4')->count();
        $spotifyWeekend = DB::table('played_tracks')->whereRaw('WEEKDAY(played_at) IN (5, 6)')->count();

        $lastfmWeekday = $this->lastfmDbQuery()->whereRaw('WEEKDAY(played_at) BETWEEN 0 AND 4')->count();
        $lastfmWeekend = $this->lastfmDbQuery()->whereRaw('WEEKDAY(played_at) IN (5, 6)')->count();

        $weekdayPlays = $spotifyWeekday + $lastfmWeekday;
        $weekendPlays = $spotifyWeekend + $lastfmWeekend;
        $totalPlays = $weekdayPlays + $weekendPlays;

        return [
            'weekday_count' => $weekdayPlays,
            'weekend_count' => $weekendPlays,
            'weekday_percentage' => $totalPlays > 0 ? round(($weekdayPlays / $totalPlays) * 100, 1) : 0,
            'weekend_percentage' => $totalPlays > 0 ? round(($weekendPlays / $totalPlays) * 100, 1) : 0,
            'preference' => $weekdayPlays > $weekendPlays ? 'weekday' : ($weekendPlays > $weekdayPlays ? 'weekend' : 'equal'),
        ];
    }

    private function getRepeatRatio(): array
    {
        $spotifyTotal = PlayedTrack::count();
        $lastfmTotal = $this->lastfmQuery()->count();
        $totalPlays = $spotifyTotal + $lastfmTotal;

        $spotifyUnique = (int) PlayedTrack::selectRaw('COUNT(DISTINCT spotify_track_id) as cnt')->value('cnt');

        $lastfmUniqueWithId = (int) $this->lastfmQuery()
            ->whereNotNull('spotify_track_id')
            ->whereNotIn('spotify_track_id', fn ($q) => $q->select('spotify_track_id')->distinct()->from('played_tracks'))
            ->selectRaw('COUNT(DISTINCT spotify_track_id) as cnt')
            ->value('cnt');

        $lastfmUniqueWithoutId = (int) $this->lastfmQuery()
            ->whereNull('spotify_track_id')
            ->selectRaw("COUNT(DISTINCT CONCAT(track_name, '|', artist_name)) as cnt")
            ->value('cnt');

        $uniqueTracks = $spotifyUnique + $lastfmUniqueWithId + $lastfmUniqueWithoutId;
        $repeatPlays = max(0, $totalPlays - $uniqueTracks);
        $repeatPercentage = $totalPlays > 0 ? round(($repeatPlays / $totalPlays) * 100, 1) : 0;

        $topSpotify = PlayedTrack::select('spotify_track_id', 'track_name', 'artist_names')
            ->selectRaw('COUNT(*) as play_count')
            ->groupBy('spotify_track_id', 'track_name', 'artist_names')
            ->orderBy('play_count', 'desc')
            ->first();

        $topLastfm = $this->lastfmQuery()
            ->whereNull('spotify_track_id')
            ->select('track_name', 'artist_name')
            ->selectRaw('COUNT(*) as play_count')
            ->groupBy('track_name', 'artist_name')
            ->orderBy('play_count', 'desc')
            ->first();

        $mostRepeated = null;
        $spotifyCount = $topSpotify?->play_count ?? 0;
        $lastfmCount = $topLastfm?->play_count ?? 0;

        if ($topSpotify && $spotifyCount >= $lastfmCount) {
            $mostRepeated = ['name' => $topSpotify->track_name, 'artists' => implode(', ', $topSpotify->artist_names), 'play_count' => $topSpotify->play_count];
        } elseif ($topLastfm) {
            $mostRepeated = ['name' => $topLastfm->track_name, 'artists' => $topLastfm->artist_name, 'play_count' => $topLastfm->play_count];
        }

        return [
            'total_plays' => $totalPlays,
            'unique_tracks' => $uniqueTracks,
            'repeat_plays' => $repeatPlays,
            'repeat_percentage' => $repeatPercentage,
            'discovery_percentage' => 100 - $repeatPercentage,
            'most_repeated_track' => $mostRepeated,
        ];
    }

    private function getBingeSessions(): array
    {
        // Last.fm scrobbles often have imprecise timestamps (batch uploads) so are excluded
        // from session detection. Cursor keeps memory bounded to one row at a time.
        $plays = DB::table('played_tracks')
            ->select('played_at', 'duration_ms', 'track_name', 'artist_names', 'album_image_url', 'spotify_track_id')
            ->orderBy('played_at')
            ->cursor();

        $sessions = [];
        $currentSession = null;
        $minSessionTracks = 5;
        $maxGapMinutes = 10;
        $maxSessionHours = 6;

        foreach ($plays as $row) {
            $playTime = Carbon::parse($row->played_at);
            $artistNames = json_decode($row->artist_names, true) ?? [];

            if ($currentSession === null) {
                $currentSession = $this->newBingeSession($row, $playTime, $artistNames);
            } else {
                $timeSinceLastPlay = abs($playTime->diffInMinutes($currentSession['end_time']));
                $sessionDuration = abs($playTime->diffInHours($currentSession['start_time']));
                $sameDay = $playTime->isSameDay($currentSession['start_time']);

                if ($timeSinceLastPlay <= $maxGapMinutes && $sessionDuration < $maxSessionHours && $sameDay) {
                    $currentSession['end_time'] = $playTime;
                    $currentSession['track_count']++;
                    $currentSession['total_duration_ms'] += $row->duration_ms;
                    $currentSession['tracks'][] = $this->bingeSessionTrack($row, $playTime, $artistNames);
                } else {
                    if ($currentSession['track_count'] >= $minSessionTracks) {
                        $sessions[] = $currentSession;
                    }
                    $currentSession = $this->newBingeSession($row, $playTime, $artistNames);
                }
            }
        }

        if ($currentSession && $currentSession['track_count'] >= $minSessionTracks) {
            $sessions[] = $currentSession;
        }

        usort($sessions, fn ($a, $b) => $b['track_count'] <=> $a['track_count']);
        $topSessions = array_slice($sessions, 0, 3);

        foreach ($topSessions as &$session) {
            $session['duration_minutes'] = round($session['total_duration_ms'] / 1000 / 60, 1);
            $session['session_length_minutes'] = $session['start_time']->diffInMinutes($session['end_time']);
        }

        return [
            'total_sessions' => count($sessions),
            'top_sessions' => $topSessions,
            'longest_session_tracks' => ! empty($topSessions) ? $topSessions[0]['track_count'] : 0,
            'total_binge_tracks' => array_sum(array_column($sessions, 'track_count')),
        ];
    }

    private function newBingeSession(object $row, Carbon $playTime, array $artistNames): array
    {
        return [
            'start_time' => $playTime,
            'end_time' => $playTime,
            'track_count' => 1,
            'total_duration_ms' => $row->duration_ms,
            'tracks' => [$this->bingeSessionTrack($row, $playTime, $artistNames)],
        ];
    }

    private function bingeSessionTrack(object $row, Carbon $playTime, array $artistNames): array
    {
        return [
            'name' => $row->track_name,
            'artists' => implode(', ', $artistNames),
            'played_at' => $playTime,
            'album_image_url' => $row->album_image_url,
            'spotify_track_id' => $row->spotify_track_id,
        ];
    }

    private function getDiscoveryRate(): array
    {
        $firstLastfmDate = $this->lastfmQuery()->min('played_at');

        $firstDate = match (true) {
            (bool) $firstLastfmDate && (bool) $this->firstSpotifyPlay => min($firstLastfmDate, $this->firstSpotifyPlay),
            (bool) $firstLastfmDate => $firstLastfmDate,
            (bool) $this->firstSpotifyPlay => $this->firstSpotifyPlay,
            default => null,
        };

        $totalDays = $firstDate ? max(1, Carbon::parse($firstDate)->diffInDays(Carbon::now()) + 1) : 1;
        $totalTracks = PlayedTrack::count() + $this->lastfmQuery()->count();

        $spotifyUnique = (int) PlayedTrack::selectRaw('COUNT(DISTINCT spotify_track_id) as cnt')->value('cnt');
        $lastfmUniqueWithId = (int) $this->lastfmQuery()
            ->whereNotNull('spotify_track_id')
            ->whereNotIn('spotify_track_id', fn ($q) => $q->select('spotify_track_id')->distinct()->from('played_tracks'))
            ->selectRaw('COUNT(DISTINCT spotify_track_id) as cnt')
            ->value('cnt');
        $lastfmUniqueWithoutId = (int) $this->lastfmQuery()
            ->whereNull('spotify_track_id')
            ->selectRaw("COUNT(DISTINCT CONCAT(track_name, '|', artist_name)) as cnt")
            ->value('cnt');
        $totalUnique = $spotifyUnique + $lastfmUniqueWithId + $lastfmUniqueWithoutId;

        $avgTracksPerDay = round($totalTracks / $totalDays, 1);
        $avgUniquePerDay = round($totalUnique / $totalDays, 1);

        $spotifyDailyQuery = DB::table('played_tracks')
            ->selectRaw('DATE(played_at) as date, COUNT(*) as total_tracks, COUNT(DISTINCT spotify_track_id) as unique_tracks')
            ->groupBy('date');

        $lastfmDailyQuery = $this->lastfmDbQuery()
            ->selectRaw("DATE(played_at) as date, COUNT(*) as total_tracks, COUNT(DISTINCT COALESCE(spotify_track_id, CONCAT(track_name, '|', artist_name))) as unique_tracks")
            ->groupBy('date');

        $recentDays = DB::query()
            ->fromSub($spotifyDailyQuery->unionAll($lastfmDailyQuery), 'combined')
            ->selectRaw('date, SUM(total_tracks) as total, SUM(unique_tracks) as unique_total')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();

        $tracksPerDay = [];
        foreach ($recentDays as $day) {
            $tracksPerDay[$day->date] = [
                'total' => (int) $day->total,
                'unique' => (int) $day->unique_total,
                'repeat' => max(0, (int) $day->total - (int) $day->unique_total),
            ];
        }
        $tracksPerDay = array_reverse($tracksPerDay, true);

        return [
            'total_days_tracked' => (int) $totalDays,
            'avg_tracks_per_day' => $avgTracksPerDay,
            'avg_unique_tracks_per_day' => $avgUniquePerDay,
            'discovery_percentage' => $avgTracksPerDay > 0 ? round(($avgUniquePerDay / $avgTracksPerDay) * 100, 1) : 0,
            'recent_days' => $tracksPerDay,
        ];
    }

    private function getTimeLabel(int $hour): string
    {
        return sprintf('%02d:00', $hour);
    }

    private function getTimeRange(int $hour): string
    {
        $nextHour = ($hour + 1) % 24;

        return sprintf('%02d:00 - %02d:00', $hour, $nextHour);
    }

    private function getTimeEmoji(int $hour): string
    {
        if ($hour >= 6 && $hour < 12) {
            return '🌅';
        } elseif ($hour >= 12 && $hour < 17) {
            return '☀️';
        } elseif ($hour >= 17 && $hour < 21) {
            return '🌆';
        } else {
            return '🌙';
        }
    }

    private function getTimeDescription(int $hour): string
    {
        if ($hour >= 6 && $hour < 9) {
            return 'Early Morning';
        } elseif ($hour >= 9 && $hour < 12) {
            return 'Late Morning';
        } elseif ($hour >= 12 && $hour < 14) {
            return 'Lunch Time';
        } elseif ($hour >= 14 && $hour < 17) {
            return 'Afternoon';
        } elseif ($hour >= 17 && $hour < 19) {
            return 'Early Evening';
        } elseif ($hour >= 19 && $hour < 21) {
            return 'Prime Time';
        } elseif ($hour >= 21 && $hour < 24) {
            return 'Late Evening';
        } else {
            return 'Night Owl';
        }
    }
}
