<?php

namespace App\Http\Controllers;

use App\Models\Mood;
use App\Models\PlayedTrack;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class MusicStatsController extends Controller
{
    /**
     * Show music statistics page
     */
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

    /**
     * Get top artists by play count
     */
    private function getTopArtists(int $limit = 10): array
    {
        $artistStats = [];

        // Get all played tracks and count artist plays
        $tracks = PlayedTrack::select('artist_names', 'played_at', 'duration_ms', 'spotify_track_id')->get();

        foreach ($tracks as $track) {
            foreach ($track->artist_names as $artist) {
                if (! isset($artistStats[$artist])) {
                    $artistStats[$artist] = [
                        'name' => $artist,
                        'play_count' => 0,
                        'total_duration_ms' => 0,
                        'unique_tracks' => [],
                        'first_played' => null,
                        'last_played' => null,
                    ];
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

        // Convert unique tracks count and sort by play count
        foreach ($artistStats as &$stats) {
            $stats['unique_tracks_count'] = count($stats['unique_tracks']);
            unset($stats['unique_tracks']);
        }

        usort($artistStats, function ($a, $b) {
            return $b['play_count'] <=> $a['play_count'];
        });

        return array_slice($artistStats, 0, $limit);
    }

    /**
     * Get top tracks by play count
     */
    private function getTopTracks(int $limit = 10): array
    {
        $tracks = PlayedTrack::select('spotify_track_id', 'track_name', 'artist_names', 'album_name', 'album_image_url', 'duration_ms')
            ->selectRaw('COUNT(*) as play_count')
            ->selectRaw('MAX(played_at) as last_played')
            ->selectRaw('MIN(played_at) as first_played')
            ->groupBy('spotify_track_id', 'track_name', 'artist_names', 'album_name', 'album_image_url', 'duration_ms')
            ->orderBy('play_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($track) {
                $track->artists_string = implode(', ', $track->artist_names);

                return $track;
            });

        // Get moods for tracks
        $trackIds = $tracks->pluck('spotify_track_id')->toArray();
        $trackMoods = PlayedTrack::getMoodsForTracks($trackIds);

        return $tracks->map(function ($track) use ($trackMoods) {
            $track->moods = $trackMoods[$track->spotify_track_id] ?? [];

            return $track;
        })->toArray();
    }

    /**
     * Get top albums by play count
     */
    private function getTopAlbums(int $limit = 10): array
    {
        return PlayedTrack::select('album_name', 'artist_names', 'album_image_url')
            ->selectRaw('COUNT(*) as play_count')
            ->selectRaw('COUNT(DISTINCT spotify_track_id) as unique_tracks_count')
            ->selectRaw('MAX(played_at) as last_played')
            ->selectRaw('MIN(played_at) as first_played')
            ->groupBy('album_name', 'artist_names', 'album_image_url')
            ->orderBy('play_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($album) {
                $album->artists_string = implode(', ', array_unique($album->artist_names));

                return $album;
            })
            ->toArray();
    }

    /**
     * Get top moods by total play count
     */
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
            ->map(function ($mood) {
                return [
                    'id' => $mood->id,
                    'name' => $mood->name,
                    'color' => $mood->color,
                    'icon' => $mood->icon,
                    'play_count' => $mood->play_count,
                    'track_count' => $mood->track_count,
                ];
            })
            ->toArray();
    }

    /**
     * Get general listening statistics
     */
    private function getListeningStats(): array
    {
        $totalTracks = PlayedTrack::count();
        $uniqueTracks = PlayedTrack::distinct('spotify_track_id')->count();
        $totalDuration = PlayedTrack::sum('duration_ms');

        $firstTrack = PlayedTrack::orderBy('played_at', 'asc')->first();
        $lastTrack = PlayedTrack::orderBy('played_at', 'desc')->first();

        // Stats for different time periods
        $todayTracks = PlayedTrack::whereDate('played_at', Carbon::today())->count();
        $weekTracks = PlayedTrack::whereBetween('played_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek(),
        ])->count();
        $monthTracks = PlayedTrack::whereMonth('played_at', Carbon::now()->month)
            ->whereYear('played_at', Carbon::now()->year)
            ->count();

        return [
            'total_tracks' => $totalTracks,
            'unique_tracks' => $uniqueTracks,
            'total_duration_ms' => $totalDuration,
            'total_duration_hours' => round($totalDuration / 1000 / 60 / 60, 1),
            'first_track_date' => $firstTrack?->played_at,
            'last_track_date' => $lastTrack?->played_at,
            'tracks_today' => $todayTracks,
            'tracks_this_week' => $weekTracks,
            'tracks_this_month' => $monthTracks,
            'average_per_day' => $firstTrack ? round($totalTracks / max(1, $firstTrack->played_at->diffInDays(Carbon::now())), 1) : 0,
        ];
    }

    /**
     * Get top 3 listening times of the day
     */
    private function getTopListeningTimes(): array
    {
        // Get all played tracks with hour information, converted to Europe/Amsterdam timezone
        $tracks = PlayedTrack::selectRaw("HOUR(CONVERT_TZ(played_at, 'UTC', 'Europe/Amsterdam')) as hour, COUNT(*) as play_count")
            ->groupBy('hour')
            ->orderBy('play_count', 'desc')
            ->limit(3)
            ->get();

        $timeStats = [];

        foreach ($tracks as $track) {
            $hour = $track->hour;
            $timeLabel = $this->getTimeLabel($hour);
            $timeRange = $this->getTimeRange($hour);

            $timeStats[] = [
                'hour' => $hour,
                'time_label' => $timeLabel,
                'time_range' => $timeRange,
                'play_count' => $track->play_count,
                'emoji' => $this->getTimeEmoji($hour),
                'description' => $this->getTimeDescription($hour),
            ];
        }

        return $timeStats;
    }

    /**
     * Get time label for an hour (24h format)
     */
    private function getTimeLabel(int $hour): string
    {
        return sprintf('%02d:00', $hour);
    }

    /**
     * Get time range for an hour
     */
    private function getTimeRange(int $hour): string
    {
        $nextHour = ($hour + 1) % 24;

        return sprintf('%02d:00 - %02d:00', $hour, $nextHour);
    }

    /**
     * Get emoji for time of day
     */
    private function getTimeEmoji(int $hour): string
    {
        if ($hour >= 6 && $hour < 12) {
            return '🌅'; // Morning
        } elseif ($hour >= 12 && $hour < 17) {
            return '☀️'; // Afternoon
        } elseif ($hour >= 17 && $hour < 21) {
            return '🌆'; // Evening
        } else {
            return '🌙'; // Night
        }
    }

    /**
     * Get description for time of day
     */
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

    /**
     * Get weekday vs weekend listening statistics
     */
    private function getWeekdayVsWeekendStats(): array
    {
        // Get weekday plays (Monday = 1, Sunday = 7)
        $weekdayPlays = PlayedTrack::selectRaw('COUNT(*) as play_count')
            ->whereRaw('WEEKDAY(played_at) BETWEEN 0 AND 4') // Monday(0) to Friday(4)
            ->first()->play_count ?? 0;

        // Get weekend plays
        $weekendPlays = PlayedTrack::selectRaw('COUNT(*) as play_count')
            ->whereRaw('WEEKDAY(played_at) IN (5, 6)') // Saturday(5) and Sunday(6)
            ->first()->play_count ?? 0;

        $totalPlays = $weekdayPlays + $weekendPlays;

        return [
            'weekday_count' => $weekdayPlays,
            'weekend_count' => $weekendPlays,
            'weekday_percentage' => $totalPlays > 0 ? round(($weekdayPlays / $totalPlays) * 100, 1) : 0,
            'weekend_percentage' => $totalPlays > 0 ? round(($weekendPlays / $totalPlays) * 100, 1) : 0,
            'preference' => $weekdayPlays > $weekendPlays ? 'weekday' : ($weekendPlays > $weekdayPlays ? 'weekend' : 'equal'),
        ];
    }

    /**
     * Get repeat ratio statistics
     */
    private function getRepeatRatio(): array
    {
        $totalPlays = PlayedTrack::count();
        $uniqueTracks = PlayedTrack::distinct('spotify_track_id')->count();

        $repeatPlays = $totalPlays - $uniqueTracks;
        $repeatPercentage = $totalPlays > 0 ? round(($repeatPlays / $totalPlays) * 100, 1) : 0;

        // Get most repeated track
        $mostRepeated = PlayedTrack::select('spotify_track_id', 'track_name', 'artist_names')
            ->selectRaw('COUNT(*) as play_count')
            ->groupBy('spotify_track_id', 'track_name', 'artist_names')
            ->orderBy('play_count', 'desc')
            ->first();

        return [
            'total_plays' => $totalPlays,
            'unique_tracks' => $uniqueTracks,
            'repeat_plays' => $repeatPlays,
            'repeat_percentage' => $repeatPercentage,
            'discovery_percentage' => 100 - $repeatPercentage,
            'most_repeated_track' => $mostRepeated ? [
                'name' => $mostRepeated->track_name,
                'artists' => implode(', ', $mostRepeated->artist_names),
                'play_count' => $mostRepeated->play_count,
            ] : null,
        ];
    }

    /**
     * Get binge sessions (periods of intensive continuous listening)
     */
    private function getBingeSessions(): array
    {
        // Get all plays ordered by time
        $plays = PlayedTrack::select('played_at', 'duration_ms', 'track_name', 'artist_names', 'album_image_url', 'spotify_track_id')
            ->orderBy('played_at')
            ->get();

        $sessions = [];
        $currentSession = null;
        $minSessionTracks = 5; // Minimum tracks for a binge session
        $maxGapMinutes = 10; // Maximum gap between tracks to stay in same session
        $maxSessionHours = 6; // Maximum duration for a single session

        foreach ($plays as $index => $play) {
            $playTime = $play->played_at;

            if ($currentSession === null) {
                // Start first session
                $currentSession = [
                    'start_time' => $playTime,
                    'end_time' => $playTime,
                    'track_count' => 1,
                    'total_duration_ms' => $play->duration_ms,
                    'tracks' => [
                        [
                            'name' => $play->track_name,
                            'artists' => implode(', ', $play->artist_names),
                            'played_at' => $playTime,
                            'album_image_url' => $play->album_image_url,
                            'spotify_track_id' => $play->spotify_track_id,
                        ],
                    ],
                ];
            } else {
                // Check if this play is within the max gap of the last play
                $timeSinceLastPlay = abs($playTime->diffInMinutes($currentSession['end_time']));
                $sessionDuration = abs($playTime->diffInHours($currentSession['start_time']));

                // Check if same day (to prevent multi-day sessions)
                $sameDay = $playTime->isSameDay($currentSession['start_time']);

                if ($timeSinceLastPlay <= $maxGapMinutes && $sessionDuration < $maxSessionHours && $sameDay) {
                    // Continue current session
                    $currentSession['end_time'] = $playTime;
                    $currentSession['track_count']++;
                    $currentSession['total_duration_ms'] += $play->duration_ms;
                    $currentSession['tracks'][] = [
                        'name' => $play->track_name,
                        'artists' => implode(', ', $play->artist_names),
                        'played_at' => $playTime,
                        'album_image_url' => $play->album_image_url,
                        'spotify_track_id' => $play->spotify_track_id,
                    ];
                } else {
                    // End current session and start new one
                    if ($currentSession['track_count'] >= $minSessionTracks) {
                        $sessions[] = $currentSession;
                    }

                    $currentSession = [
                        'start_time' => $playTime,
                        'end_time' => $playTime,
                        'track_count' => 1,
                        'total_duration_ms' => $play->duration_ms,
                        'tracks' => [
                            [
                                'name' => $play->track_name,
                                'artists' => implode(', ', $play->artist_names),
                                'played_at' => $playTime,
                                'album_image_url' => $play->album_image_url,
                                'spotify_track_id' => $play->spotify_track_id,
                            ],
                        ],
                    ];
                }
            }
        }

        // Don't forget the last session
        if ($currentSession && $currentSession['track_count'] >= $minSessionTracks) {
            $sessions[] = $currentSession;
        }

        // Sort sessions by track count (longest first)
        usort($sessions, function ($a, $b) {
            return $b['track_count'] <=> $a['track_count'];
        });

        // Get top 3 sessions
        $topSessions = array_slice($sessions, 0, 3);

        // Calculate session durations in minutes
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

    /**
     * Get discovery rate (new vs known tracks)
     */
    private function getDiscoveryRate(): array
    {
        $totalDays = PlayedTrack::selectRaw('DATEDIFF(MAX(played_at), MIN(played_at)) + 1 as total_days')
            ->first()->total_days ?? 1;

        $tracksPerDay = [];
        $discoveredTracks = [];

        // Get daily discovery stats
        $dailyStats = PlayedTrack::selectRaw('DATE(played_at) as date, COUNT(*) as total_tracks, COUNT(DISTINCT spotify_track_id) as unique_tracks')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        foreach ($dailyStats as $day) {
            $date = $day->date;
            $tracksPerDay[$date] = [
                'total' => $day->total_tracks,
                'unique' => $day->unique_tracks,
                'repeat' => $day->total_tracks - $day->unique_tracks,
            ];
        }

        // Calculate overall discovery metrics
        $avgTracksPerDay = PlayedTrack::count() / max(1, $totalDays);
        $avgUniquePerDay = PlayedTrack::distinct('spotify_track_id')->count() / max(1, $totalDays);

        return [
            'total_days_tracked' => (int) $totalDays,
            'avg_tracks_per_day' => round($avgTracksPerDay, 1),
            'avg_unique_tracks_per_day' => round($avgUniquePerDay, 1),
            'discovery_percentage' => $avgTracksPerDay > 0 ? round(($avgUniquePerDay / $avgTracksPerDay) * 100, 1) : 0,
            'recent_days' => array_slice($tracksPerDay, -7, 7, true), // Last 7 days
        ];
    }
}
