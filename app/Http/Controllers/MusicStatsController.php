<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MusicStatsController extends Controller
{
    public function index(): View
    {
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

    private function getTopMoods(int $limit = 10): array
    {
        return DB::table('track_mood')
            ->join('moods', 'moods.id', '=', 'track_mood.mood_id')
            ->join('plays', 'plays.track_id', '=', 'track_mood.track_id')
            ->where('moods.is_active', true)
            ->select('moods.id', 'moods.name', 'moods.color', 'moods.icon')
            ->selectRaw('COUNT(DISTINCT track_mood.track_id) as track_count')
            ->selectRaw('COUNT(plays.id) as play_count')
            ->groupBy('moods.id', 'moods.name', 'moods.color', 'moods.icon')
            ->orderByDesc('play_count')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    private function getTopArtists(int $limit = 10): array
    {
        return DB::table('plays')
            ->join('track_artists', 'track_artists.track_id', '=', 'plays.track_id')
            ->join('artists', 'artists.id', '=', 'track_artists.artist_id')
            ->join('tracks', 'tracks.id', '=', 'plays.track_id')
            ->select('artists.name')
            ->selectRaw('COUNT(plays.id) as play_count')
            ->selectRaw('SUM(COALESCE(tracks.duration_ms, 0)) as total_duration_ms')
            ->selectRaw('COUNT(DISTINCT plays.track_id) as unique_tracks_count')
            ->selectRaw('MIN(plays.played_at) as first_played')
            ->selectRaw('MAX(plays.played_at) as last_played')
            ->groupBy('artists.id', 'artists.name')
            ->orderByDesc('play_count')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'play_count' => $row->play_count,
                'total_duration_ms' => $row->total_duration_ms,
                'unique_tracks_count' => $row->unique_tracks_count,
                'first_played' => $row->first_played ? Carbon::parse($row->first_played) : null,
                'last_played' => $row->last_played ? Carbon::parse($row->last_played) : null,
            ])
            ->all();
    }

    private function getTopTracks(int $limit = 10): array
    {
        return DB::table('plays')
            ->join('tracks', 'tracks.id', '=', 'plays.track_id')
            ->leftJoin('albums', 'albums.id', '=', 'tracks.album_id')
            ->leftJoin('track_artists', function ($join) {
                $join->on('track_artists.track_id', '=', 'tracks.id')
                    ->where('track_artists.is_primary', true);
            })
            ->leftJoin('artists', 'artists.id', '=', 'track_artists.artist_id')
            ->select(
                'tracks.id',
                'tracks.title as track_name',
                'tracks.spotify_track_id',
                'tracks.duration_ms',
                'albums.name as album_name',
                'albums.image_url as album_image_url',
                DB::raw("COALESCE(artists.name, '') as artists_string"),
            )
            ->selectRaw('COUNT(plays.id) as play_count')
            ->selectRaw('MIN(plays.played_at) as first_played')
            ->selectRaw('MAX(plays.played_at) as last_played')
            ->groupBy('tracks.id', 'tracks.title', 'tracks.spotify_track_id', 'tracks.duration_ms', 'albums.name', 'albums.image_url', 'artists.name')
            ->orderByDesc('play_count')
            ->limit($limit)
            ->get()
            ->map(fn ($track) => array_merge(
                (array) $track,
                ['moods' => [], 'artist_names' => [$track->artists_string]]
            ))
            ->all();
    }

    private function getTopAlbums(int $limit = 10): array
    {
        return DB::table('plays')
            ->join('tracks', 'tracks.id', '=', 'plays.track_id')
            ->join('albums', 'albums.id', '=', 'tracks.album_id')
            ->leftJoin('track_artists', function ($join) {
                $join->on('track_artists.track_id', '=', 'tracks.id')
                    ->where('track_artists.is_primary', true);
            })
            ->leftJoin('artists', 'artists.id', '=', 'track_artists.artist_id')
            ->select(
                'albums.name as album_name',
                'albums.image_url as album_image_url',
                DB::raw("COALESCE(artists.name, '') as artists_string"),
            )
            ->selectRaw('COUNT(plays.id) as play_count')
            ->selectRaw('COUNT(DISTINCT plays.track_id) as unique_tracks_count')
            ->groupBy('albums.id', 'albums.name', 'albums.image_url', 'artists.name')
            ->orderByDesc('play_count')
            ->limit($limit)
            ->get()
            ->map(fn ($album) => (array) $album)
            ->all();
    }

    private function getListeningStats(): array
    {
        $totalPlays = DB::table('plays')->count();
        $uniqueTracks = DB::table('plays')->distinct('track_id')->count('track_id');
        $totalDurationMs = (int) DB::table('plays')
            ->join('tracks', 'tracks.id', '=', 'plays.track_id')
            ->sum('tracks.duration_ms');
        $firstPlay = DB::table('plays')->min('played_at');
        $lastPlay = DB::table('plays')->max('played_at');

        $todayPlays = DB::table('plays')->whereDate('played_at', Carbon::today())->count();
        $weekPlays = DB::table('plays')->whereBetween('played_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $monthPlays = DB::table('plays')->whereMonth('played_at', now()->month)->whereYear('played_at', now()->year)->count();

        $firstPlayDate = $firstPlay ? Carbon::parse($firstPlay) : null;

        return [
            'total_tracks' => $totalPlays,
            'unique_tracks' => $uniqueTracks,
            'total_duration_ms' => $totalDurationMs,
            'total_duration_hours' => round($totalDurationMs / 1000 / 60 / 60, 1),
            'first_track_date' => $firstPlayDate,
            'last_track_date' => $lastPlay ? Carbon::parse($lastPlay) : null,
            'tracks_today' => $todayPlays,
            'tracks_this_week' => $weekPlays,
            'tracks_this_month' => $monthPlays,
            'average_per_day' => $firstPlayDate ? round($totalPlays / max(1, $firstPlayDate->diffInDays(now())), 1) : 0,
        ];
    }

    private function getTopListeningTimes(): array
    {
        $tracks = DB::table('plays')
            ->selectRaw('HOUR(DATE_ADD(played_at, INTERVAL 1 HOUR)) as hour, COUNT(*) as play_count')
            ->groupBy('hour')
            ->orderByDesc('play_count')
            ->limit(3)
            ->get();

        return $tracks->map(function ($track) {
            $hour = (int) ($track->hour ?? 0);

            return [
                'hour' => $hour,
                'time_label' => sprintf('%02d:00', $hour),
                'time_range' => sprintf('%02d:00 - %02d:00', $hour, ($hour + 1) % 24),
                'play_count' => $track->play_count,
                'emoji' => $this->getTimeEmoji($hour),
                'description' => $this->getTimeDescription($hour),
            ];
        })->toArray();
    }

    private function getWeekdayVsWeekendStats(): array
    {
        $weekdayPlays = DB::table('plays')->whereRaw('WEEKDAY(played_at) BETWEEN 0 AND 4')->count();
        $weekendPlays = DB::table('plays')->whereRaw('WEEKDAY(played_at) IN (5, 6)')->count();
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
        $totalPlays = DB::table('plays')->count();
        $uniqueTracks = DB::table('plays')->distinct('track_id')->count('track_id');
        $repeatPlays = max(0, $totalPlays - $uniqueTracks);
        $repeatPercentage = $totalPlays > 0 ? round(($repeatPlays / $totalPlays) * 100, 1) : 0;

        $mostRepeatedRow = DB::table('plays')
            ->join('tracks', 'tracks.id', '=', 'plays.track_id')
            ->leftJoin('track_artists', function ($join) {
                $join->on('track_artists.track_id', '=', 'tracks.id')
                    ->where('track_artists.is_primary', true);
            })
            ->leftJoin('artists', 'artists.id', '=', 'track_artists.artist_id')
            ->select('tracks.title as name', DB::raw("COALESCE(artists.name, '') as artists"))
            ->selectRaw('COUNT(plays.id) as play_count')
            ->groupBy('tracks.id', 'tracks.title', 'artists.name')
            ->orderByDesc('play_count')
            ->first();

        $mostRepeated = $mostRepeatedRow ? [
            'name' => $mostRepeatedRow->name,
            'artists' => $mostRepeatedRow->artists,
            'play_count' => $mostRepeatedRow->play_count,
        ] : null;

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
        // Only Spotify plays have reliable per-track timestamps
        $plays = DB::table('plays')
            ->join('tracks', 'tracks.id', '=', 'plays.track_id')
            ->leftJoin('albums', 'albums.id', '=', 'tracks.album_id')
            ->leftJoin('track_artists', function ($join) {
                $join->on('track_artists.track_id', '=', 'tracks.id')
                    ->where('track_artists.is_primary', true);
            })
            ->leftJoin('artists', 'artists.id', '=', 'track_artists.artist_id')
            ->where('plays.source', 'spotify')
            ->select(
                'plays.played_at',
                'tracks.duration_ms',
                'tracks.title as track_name',
                'tracks.spotify_track_id',
                'albums.image_url as album_image_url',
                DB::raw("COALESCE(artists.name, '') as artist_names"),
            )
            ->orderBy('plays.played_at')
            ->cursor();

        $sessions = [];
        $currentSession = null;
        $minSessionTracks = 5;
        $maxGapMinutes = 10;
        $maxSessionHours = 6;

        foreach ($plays as $row) {
            $playTime = Carbon::parse($row->played_at);

            if ($currentSession === null) {
                $currentSession = $this->newBingeSession($row, $playTime);
            } else {
                $timeSinceLastPlay = abs($playTime->diffInMinutes($currentSession['end_time']));
                $sessionDuration = abs($playTime->diffInHours($currentSession['start_time']));
                $sameDay = $playTime->isSameDay($currentSession['start_time']);

                if ($timeSinceLastPlay <= $maxGapMinutes && $sessionDuration < $maxSessionHours && $sameDay) {
                    $currentSession['end_time'] = $playTime;
                    $currentSession['track_count']++;
                    $currentSession['total_duration_ms'] += $row->duration_ms;
                    $currentSession['tracks'][] = $this->bingeSessionTrack($row, $playTime);
                } else {
                    if ($currentSession['track_count'] >= $minSessionTracks) {
                        $sessions[] = $currentSession;
                    }
                    $currentSession = $this->newBingeSession($row, $playTime);
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

    private function newBingeSession(object $row, Carbon $playTime): array
    {
        return [
            'start_time' => $playTime,
            'end_time' => $playTime,
            'track_count' => 1,
            'total_duration_ms' => $row->duration_ms,
            'tracks' => [$this->bingeSessionTrack($row, $playTime)],
        ];
    }

    private function bingeSessionTrack(object $row, Carbon $playTime): array
    {
        return [
            'name' => $row->track_name,
            'artists' => $row->artist_names,
            'played_at' => $playTime,
            'album_image_url' => $row->album_image_url,
            'spotify_track_id' => $row->spotify_track_id,
        ];
    }

    private function getDiscoveryRate(): array
    {
        $firstDate = DB::table('plays')->min('played_at');
        $firstPlayDate = $firstDate ? Carbon::parse($firstDate) : null;
        $totalDays = $firstPlayDate ? max(1, $firstPlayDate->diffInDays(now()) + 1) : 1;
        $totalTracks = DB::table('plays')->count();
        $totalUnique = DB::table('plays')->distinct('track_id')->count('track_id');

        $avgTracksPerDay = round($totalTracks / $totalDays, 1);
        $avgUniquePerDay = round($totalUnique / $totalDays, 1);

        $recentDays = DB::table('plays')
            ->selectRaw('DATE(played_at) as date, COUNT(*) as total, COUNT(DISTINCT track_id) as unique_total')
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
