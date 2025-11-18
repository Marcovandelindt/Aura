<?php

namespace App\Http\Controllers;

use App\Models\PlayStationGame;
use App\Models\PlayStationSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlayStationStatsController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->get('filter', '30');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $dateRange = $this->getDateRange($filter, $startDate, $endDate);

        $stats = [
            'overview' => $this->getOverviewStats(),
            'top_games' => $this->getTopGames(),
            'top_start_hours' => $this->getTopStartHours(),
            'weekday_vs_weekend' => $this->getWeekdayVsWeekendStats(),
            'monthly_stats' => $this->getMonthlyStats(),
            'platform_stats' => $this->getPlatformStats(),
            'longest_session' => $this->getLongestSession(),
            'shortest_session' => $this->getShortestSession(),
            'earliest_session' => $this->getEarliestSession(),
            'latest_session' => $this->getLatestSession(),
            'longest_streak' => $this->getLongestStreak(),
            'current_streak' => $this->getCurrentStreak(),
            'late_night_sessions' => $this->getLateNightSessions(),
            'abandoned_games' => $this->getAbandonedGames(),
            'recent_activity' => $this->getRecentActivity(),
            'yearly_comparison' => $this->getYearlyComparison(),
            'avg_session_by_day' => $this->getAvgSessionByDay(),
            'daily_playtime' => $this->getDailyPlaytime($dateRange['start'], $dateRange['end']),
        ];

        $filterData = [
            'current' => $filter,
            'start_date' => $dateRange['start']?->format('Y-m-d'),
            'end_date' => $dateRange['end']?->format('Y-m-d'),
        ];

        return view('playstation.stats', compact('stats', 'filterData'));
    }

    private function getDateRange(string $filter, ?string $startDate, ?string $endDate): array
    {
        if ($filter === 'custom' && $startDate && $endDate) {
            return [
                'start' => Carbon::parse($startDate)->startOfDay(),
                'end' => Carbon::parse($endDate)->endOfDay(),
            ];
        }

        if ($filter === 'single' && $startDate) {
            return [
                'start' => Carbon::parse($startDate)->startOfDay(),
                'end' => Carbon::parse($startDate)->endOfDay(),
            ];
        }

        $end = now()->endOfDay();

        return match ($filter) {
            '7' => ['start' => now()->subDays(6)->startOfDay(), 'end' => $end],
            '30' => ['start' => now()->subDays(29)->startOfDay(), 'end' => $end],
            '90' => ['start' => now()->subDays(89)->startOfDay(), 'end' => $end],
            '365' => ['start' => now()->subDays(364)->startOfDay(), 'end' => $end],
            'all' => ['start' => null, 'end' => null],
            default => ['start' => now()->subDays(29)->startOfDay(), 'end' => $end],
        };
    }

    private function getDailyPlaytime(?Carbon $startDate, ?Carbon $endDate): array
    {
        $query = PlayStationSession::with('game');

        if ($startDate) {
            $query->where('started_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('started_at', '<=', $endDate);
        }

        return $query->get()
            ->groupBy(fn ($session) => $session->started_at->format('Y-m-d'))
            ->map(function ($sessions, $date) {
                $games = $sessions->groupBy('play_station_game_id')
                    ->map(function ($gameSessions) {
                        $game = $gameSessions->first()->game;
                        $minutes = $gameSessions->sum('duration_minutes');

                        return [
                            'name' => $game?->name ?? 'Unknown',
                            'minutes' => $minutes,
                            'hours' => round($minutes / 60, 1),
                            'sessions' => $gameSessions->count(),
                        ];
                    })
                    ->sortByDesc('hours')
                    ->values()
                    ->toArray();

                return [
                    'date' => $date,
                    'date_formatted' => Carbon::parse($date)->format('D d M Y'),
                    'sessions' => $sessions->count(),
                    'minutes' => $sessions->sum('duration_minutes'),
                    'hours' => round($sessions->sum('duration_minutes') / 60, 1),
                    'games' => $games,
                ];
            })
            ->sortByDesc('date')
            ->values()
            ->toArray();
    }

    private function getOverviewStats(): array
    {
        $totalSessions = PlayStationSession::count();
        $totalMinutes = PlayStationSession::sum('duration_minutes');
        $totalGames = PlayStationGame::count();
        $uniqueGamesPlayed = PlayStationSession::distinct('play_station_game_id')->count();

        $firstSession = PlayStationSession::orderBy('started_at')->first();
        $lastSession = PlayStationSession::orderByDesc('started_at')->first();

        $thisWeek = PlayStationSession::where('started_at', '>=', now()->startOfWeek())->count();
        $thisMonth = PlayStationSession::whereMonth('started_at', now()->month)
            ->whereYear('started_at', now()->year)
            ->count();

        $totalDays = $firstSession ? $firstSession->started_at->diffInDays(now()) + 1 : 1;

        return [
            'total_sessions' => $totalSessions,
            'total_hours' => round($totalMinutes / 60, 1),
            'total_games' => $totalGames,
            'unique_games_played' => $uniqueGamesPlayed,
            'first_session_date' => $firstSession?->started_at,
            'last_session_date' => $lastSession?->started_at,
            'sessions_this_week' => $thisWeek,
            'sessions_this_month' => $thisMonth,
            'avg_sessions_per_week' => round($totalSessions / max(1, $totalDays / 7), 1),
            'avg_hours_per_week' => round(($totalMinutes / 60) / max(1, $totalDays / 7), 1),
        ];
    }

    private function getTopGames(int $limit = 10): array
    {
        return PlayStationGame::orderByDesc('hours')
            ->limit($limit)
            ->get()
            ->map(fn ($game) => [
                'id' => $game->id,
                'name' => $game->name,
                'platform' => $game->platform,
                'hours' => $game->hours,
                'sessions' => $game->sessions,
                'image_url' => $game->image_url,
                'last_played_at' => $game->last_played_at,
                'completion_percentage' => $game->completion_percentage,
            ])
            ->toArray();
    }

    private function getTopStartHours(): array
    {
        return PlayStationSession::selectRaw('HOUR(started_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'hour' => $row->hour,
                'count' => $row->count,
                'time_range' => sprintf('%02d:00 - %02d:00', $row->hour, $row->hour + 1),
                'description' => $this->getTimeDescription($row->hour),
            ])
            ->toArray();
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

    private function getWeekdayVsWeekendStats(): array
    {
        $weekdayMinutes = PlayStationSession::selectRaw('SUM(duration_minutes) as total')
            ->whereRaw('WEEKDAY(started_at) BETWEEN 0 AND 4')
            ->first()->total ?? 0;

        $weekendMinutes = PlayStationSession::selectRaw('SUM(duration_minutes) as total')
            ->whereRaw('WEEKDAY(started_at) IN (5, 6)')
            ->first()->total ?? 0;

        $weekdaySessions = PlayStationSession::whereRaw('WEEKDAY(started_at) BETWEEN 0 AND 4')->count();
        $weekendSessions = PlayStationSession::whereRaw('WEEKDAY(started_at) IN (5, 6)')->count();

        $totalMinutes = $weekdayMinutes + $weekendMinutes;

        return [
            'weekday_hours' => round($weekdayMinutes / 60, 1),
            'weekend_hours' => round($weekendMinutes / 60, 1),
            'weekday_sessions' => $weekdaySessions,
            'weekend_sessions' => $weekendSessions,
            'weekday_percentage' => $totalMinutes > 0 ? round(($weekdayMinutes / $totalMinutes) * 100, 1) : 0,
            'weekend_percentage' => $totalMinutes > 0 ? round(($weekendMinutes / $totalMinutes) * 100, 1) : 0,
            'preference' => $weekdayMinutes > $weekendMinutes ? 'weekday' : ($weekendMinutes > $weekdayMinutes ? 'weekend' : 'equal'),
        ];
    }

    private function getMonthlyStats(): array
    {
        return PlayStationSession::selectRaw('YEAR(started_at) as year, MONTH(started_at) as month, COUNT(*) as sessions, SUM(duration_minutes) as minutes')
            ->groupBy('year', 'month')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->limit(12)
            ->get()
            ->map(fn ($row) => [
                'year' => $row->year,
                'month' => $row->month,
                'month_name' => Carbon::createFromDate($row->year, $row->month, 1)->format('M Y'),
                'sessions' => $row->sessions,
                'hours' => round($row->minutes / 60, 1),
            ])
            ->toArray();
    }

    private function getPlatformStats(): array
    {
        return PlayStationGame::selectRaw('platform, COUNT(*) as games, SUM(hours) as total_hours, SUM(sessions) as total_sessions')
            ->groupBy('platform')
            ->orderByDesc('total_hours')
            ->get()
            ->map(fn ($row) => [
                'platform' => $row->platform,
                'games' => $row->games,
                'hours' => round($row->total_hours, 1),
                'sessions' => $row->total_sessions,
            ])
            ->toArray();
    }

    private function getLongestSession(): ?array
    {
        $session = PlayStationSession::with('game')
            ->orderByDesc('duration_minutes')
            ->first();

        if (! $session) {
            return null;
        }

        return [
            'duration_minutes' => $session->duration_minutes,
            'hours' => floor($session->duration_minutes / 60),
            'minutes' => $session->duration_minutes % 60,
            'game_name' => $session->game?->name ?? 'Unknown',
            'platform' => $session->game?->platform,
            'date' => $session->started_at,
        ];
    }

    private function getShortestSession(): ?array
    {
        $session = PlayStationSession::with('game')
            ->where('duration_minutes', '>', 0)
            ->orderBy('duration_minutes')
            ->first();

        if (! $session) {
            return null;
        }

        return [
            'duration_minutes' => $session->duration_minutes,
            'game_name' => $session->game?->name ?? 'Unknown',
            'platform' => $session->game?->platform,
            'date' => $session->started_at,
        ];
    }

    private function getEarliestSession(): ?array
    {
        // Early starts at 06:00, so order: 06:00 -> 23:59 -> 00:00 -> 05:59
        // Shift hours by 18 to get correct order: (hour + 18) % 24
        $session = PlayStationSession::with('game')
            ->orderByRaw('(HOUR(started_at) + 18) % 24 ASC, MINUTE(started_at) ASC')
            ->first();

        if (! $session) {
            return null;
        }

        return [
            'time' => $session->started_at->format('H:i'),
            'game_name' => $session->game?->name ?? 'Unknown',
            'platform' => $session->game?->platform,
            'date' => $session->started_at,
            'day_name' => $session->started_at->format('l'),
        ];
    }

    private function getLatestSession(): ?array
    {
        // Late ends at 05:59, so order: 06:00 -> 23:59 -> 00:00 -> 05:59
        // Shift hours by 18 to get correct order: (hour + 18) % 24
        $session = PlayStationSession::with('game')
            ->orderByRaw('(HOUR(started_at) + 18) % 24 DESC, MINUTE(started_at) DESC')
            ->first();

        if (! $session) {
            return null;
        }

        return [
            'time' => $session->started_at->format('H:i'),
            'game_name' => $session->game?->name ?? 'Unknown',
            'platform' => $session->game?->platform,
            'date' => $session->started_at,
            'day_name' => $session->started_at->format('l'),
        ];
    }

    private function getLongestStreak(): array
    {
        $sessions = PlayStationSession::with('game')
            ->orderBy('started_at')
            ->get()
            ->groupBy('play_station_game_id');

        $longestStreak = [
            'days' => 0,
            'game' => null,
            'start_date' => null,
            'end_date' => null,
        ];

        foreach ($sessions as $gameSessions) {
            $dates = $gameSessions
                ->map(fn ($s) => $s->started_at->toDateString())
                ->unique()
                ->sort()
                ->values();

            if ($dates->count() < 2) {
                continue;
            }

            $currentStreak = 1;
            $maxStreak = 1;
            $streakStart = 0;
            $maxStreakStart = 0;

            for ($i = 1; $i < $dates->count(); $i++) {
                $prevDate = Carbon::parse($dates[$i - 1]);
                $currDate = Carbon::parse($dates[$i]);

                if ($prevDate->addDay()->toDateString() === $currDate->toDateString()) {
                    $currentStreak++;
                    if ($currentStreak > $maxStreak) {
                        $maxStreak = $currentStreak;
                        $maxStreakStart = $streakStart;
                    }
                } else {
                    $currentStreak = 1;
                    $streakStart = $i;
                }
            }

            if ($maxStreak > $longestStreak['days']) {
                $longestStreak = [
                    'days' => $maxStreak,
                    'game' => $gameSessions->first()->game,
                    'start_date' => $dates[$maxStreakStart],
                    'end_date' => $dates[$maxStreakStart + $maxStreak - 1],
                ];
            }
        }

        return $longestStreak;
    }

    private function getCurrentStreak(): array
    {
        $dates = PlayStationSession::selectRaw('DATE(started_at) as date')
            ->groupBy('date')
            ->orderByDesc('date')
            ->pluck('date')
            ->toArray();

        if (empty($dates)) {
            return ['days' => 0, 'start_date' => null];
        }

        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        // Check if streak is active (played today or yesterday)
        if ($dates[0] !== $today && $dates[0] !== $yesterday) {
            return ['days' => 0, 'start_date' => null];
        }

        $streak = 1;
        for ($i = 1; $i < count($dates); $i++) {
            $prevDate = Carbon::parse($dates[$i - 1]);
            $currDate = Carbon::parse($dates[$i]);

            if ($prevDate->subDay()->toDateString() === $currDate->toDateString()) {
                $streak++;
            } else {
                break;
            }
        }

        return [
            'days' => $streak,
            'start_date' => $dates[$streak - 1] ?? $dates[0],
        ];
    }

    private function getLateNightSessions(): array
    {
        $lateNightSessions = PlayStationSession::with('game')
            ->whereRaw('HOUR(started_at) >= 0 AND HOUR(started_at) < 6')
            ->count();

        $totalSessions = PlayStationSession::count();

        $topLateNightGame = PlayStationSession::with('game')
            ->selectRaw('play_station_game_id, COUNT(*) as count')
            ->whereRaw('HOUR(started_at) >= 0 AND HOUR(started_at) < 6')
            ->groupBy('play_station_game_id')
            ->orderByDesc('count')
            ->first();

        return [
            'count' => $lateNightSessions,
            'percentage' => $totalSessions > 0 ? round(($lateNightSessions / $totalSessions) * 100, 1) : 0,
            'top_game' => $topLateNightGame?->game?->name,
        ];
    }

    private function getAbandonedGames(int $daysThreshold = 90): array
    {
        $threshold = now()->subDays($daysThreshold);

        return PlayStationGame::where('last_played_at', '<', $threshold)
            ->where('hours', '>', 1)
            ->orderByDesc('hours')
            ->limit(5)
            ->get()
            ->map(fn ($game) => [
                'name' => $game->name,
                'platform' => $game->platform,
                'hours' => $game->hours,
                'last_played_at' => $game->last_played_at,
                'days_since' => $game->last_played_at?->diffInDays(now()),
            ])
            ->toArray();
    }

    private function getRecentActivity(): array
    {
        $thirtyDaysAgo = now()->subDays(30);

        return PlayStationSession::selectRaw('DATE(started_at) as date, COUNT(*) as sessions, SUM(duration_minutes) as minutes')
            ->where('started_at', '>=', $thirtyDaysAgo)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date,
                'sessions' => $row->sessions,
                'hours' => round($row->minutes / 60, 1),
            ])
            ->toArray();
    }

    private function getYearlyComparison(): array
    {
        return PlayStationSession::selectRaw('YEAR(started_at) as year, COUNT(*) as sessions, SUM(duration_minutes) as minutes')
            ->groupBy('year')
            ->orderByDesc('year')
            ->get()
            ->map(fn ($row) => [
                'year' => $row->year,
                'sessions' => $row->sessions,
                'hours' => round($row->minutes / 60, 1),
            ])
            ->toArray();
    }

    private function getAvgSessionByDay(): array
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        // Get total minutes per weekday and count unique days
        $stats = PlayStationSession::selectRaw('WEEKDAY(started_at) as day_num, SUM(duration_minutes) as total_minutes, COUNT(*) as sessions, COUNT(DISTINCT DATE(started_at)) as unique_days')
            ->groupBy('day_num')
            ->orderBy('day_num')
            ->get()
            ->keyBy('day_num');

        $result = [];
        for ($i = 0; $i < 7; $i++) {
            $totalMinutes = $stats[$i]->total_minutes ?? 0;
            $uniqueDays = $stats[$i]->unique_days ?? 1;
            $avgMinutes = $uniqueDays > 0 ? $totalMinutes / $uniqueDays : 0;

            $result[] = [
                'day' => $days[$i],
                'day_short' => substr($days[$i], 0, 3),
                'total_minutes' => round($totalMinutes),
                'total_hours' => round($totalMinutes / 60, 1),
                'avg_minutes' => round($avgMinutes),
                'avg_hours' => round($avgMinutes / 60, 1),
                'sessions' => $stats[$i]->sessions ?? 0,
                'unique_days' => $uniqueDays,
            ];
        }

        return $result;
    }
}
