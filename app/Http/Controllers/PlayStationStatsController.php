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
            // Money & Value stats
            'money_stats' => $this->getMoneyStats(),
            'best_value_games' => $this->getBestValueGames(),
            'worst_value_games' => $this->getWorstValueGames(),
            // Genre stats
            'genre_stats' => $this->getGenreStats(),
            // Play patterns
            'session_length_distribution' => $this->getSessionLengthDistribution(),
            'marathon_sessions' => $this->getMarathonSessions(),
            'micro_sessions' => $this->getMicroSessions(),
            'gaming_heatmap' => $this->getGamingHeatmap(),
            'biggest_gaming_day' => $this->getBiggestGamingDay(),
            'most_active_month' => $this->getMostActiveMonth(),
            'days_since_last_session' => $this->getDaysSinceLastSession(),
            'avg_games_per_month' => $this->getAvgGamesPerMonth(),
            // Comparisons & Trends
            'month_comparison' => $this->getMonthComparison(),
            'library_growth' => $this->getLibraryGrowth(),
            // Fun/Trivia
            'first_game_played' => $this->getFirstGamePlayed(),
            'longest_ago_played' => $this->getLongestAgoPlayed(),
            'one_hit_wonders' => $this->getOneHitWonders(),
            'loyal_games' => $this->getLoyalGames(),
            'seasonal_patterns' => $this->getSeasonalPatterns(),
            'game_hopping_rate' => $this->getGameHoppingRate(),
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
        $sessionMinutes = PlayStationSession::sum('duration_minutes');
        $manualMinutes = PlayStationGame::sum('manual_minutes') ?? 0;
        $totalMinutes = $sessionMinutes + $manualMinutes;
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
            'manual_hours' => round($manualMinutes / 60, 1),
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
        return PlayStationGame::withSum('sessions', 'duration_minutes')
            ->withCount('sessions')
            ->orderByDesc('sessions_sum_duration_minutes')
            ->limit($limit)
            ->get()
            ->map(fn ($game) => [
                'id' => $game->id,
                'name' => $game->name,
                'platform' => $game->platform,
                'hours' => $game->calculated_hours,
                'sessions' => $game->calculated_sessions,
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
                'time_range' => sprintf('%02d:00 - %02d:00', $row->hour, ($row->hour + 1) % 24),
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
        return PlayStationSession::join('play_station_games', 'play_station_sessions.play_station_game_id', '=', 'play_station_games.id')
            ->selectRaw('play_station_games.platform, COUNT(DISTINCT play_station_games.id) as games, COUNT(*) as total_sessions, SUM(play_station_sessions.duration_minutes) as total_minutes')
            ->groupBy('play_station_games.platform')
            ->orderByDesc('total_minutes')
            ->get()
            ->map(fn ($row) => [
                'platform' => $row->platform,
                'games' => $row->games,
                'hours' => round(($row->total_minutes ?? 0) / 60, 1),
                'sessions' => $row->total_sessions ?? 0,
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

        return PlayStationGame::withSum('sessions', 'duration_minutes')
            ->where('last_played_at', '<', $threshold)
            ->having('sessions_sum_duration_minutes', '>', 60) // More than 1 hour
            ->orderByDesc('sessions_sum_duration_minutes')
            ->limit(5)
            ->get()
            ->map(fn ($game) => [
                'name' => $game->name,
                'platform' => $game->platform,
                'hours' => $game->calculated_hours,
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

    // ========================================
    // Money & Value Statistics
    // ========================================

    private function getMoneyStats(): array
    {
        $games = PlayStationGame::whereNotNull('price')->where('price', '>', 0)->get();
        $totalSpent = $games->sum('price');
        $gamesWithPrice = $games->count();
        $avgPrice = $gamesWithPrice > 0 ? $totalSpent / $gamesWithPrice : 0;

        $totalMinutes = PlayStationSession::sum('duration_minutes') + (PlayStationGame::sum('manual_minutes') ?? 0);
        $totalHours = $totalMinutes / 60;
        $costPerHour = $totalHours > 0 ? $totalSpent / $totalHours : 0;

        return [
            'total_spent' => round($totalSpent, 2),
            'games_with_price' => $gamesWithPrice,
            'avg_price' => round($avgPrice, 2),
            'cost_per_hour' => round($costPerHour, 2),
            'total_hours' => round($totalHours, 1),
        ];
    }

    private function getBestValueGames(int $limit = 5): array
    {
        return PlayStationGame::withSum('sessions', 'duration_minutes')
            ->whereNotNull('price')
            ->where('price', '>', 0)
            ->get()
            ->map(function ($game) {
                $totalMinutes = ($game->sessions_sum_duration_minutes ?? 0) + ($game->manual_minutes ?? 0);
                $hours = $totalMinutes / 60;
                $costPerHour = $hours > 0 ? $game->price / $hours : PHP_INT_MAX;

                return [
                    'name' => $game->name,
                    'platform' => $game->platform,
                    'price' => $game->price,
                    'hours' => round($hours, 1),
                    'cost_per_hour' => round($costPerHour, 2),
                    'image_url' => $game->image_url,
                ];
            })
            ->filter(fn ($game) => $game['hours'] > 0)
            ->sortBy('cost_per_hour')
            ->take($limit)
            ->values()
            ->toArray();
    }

    private function getWorstValueGames(int $limit = 5): array
    {
        return PlayStationGame::withSum('sessions', 'duration_minutes')
            ->whereNotNull('price')
            ->where('price', '>', 0)
            ->get()
            ->map(function ($game) {
                $totalMinutes = ($game->sessions_sum_duration_minutes ?? 0) + ($game->manual_minutes ?? 0);
                $hours = $totalMinutes / 60;

                return [
                    'name' => $game->name,
                    'platform' => $game->platform,
                    'price' => $game->price,
                    'hours' => round($hours, 1),
                    'image_url' => $game->image_url,
                ];
            })
            ->filter(fn ($game) => $game['hours'] < 1) // Less than 1 hour played
            ->sortByDesc('price')
            ->take($limit)
            ->values()
            ->toArray();
    }

    // ========================================
    // Genre Statistics
    // ========================================

    private function getGenreStats(): array
    {
        $games = PlayStationGame::with('genres')
            ->withSum('sessions', 'duration_minutes')
            ->get();

        $genreStats = [];

        foreach ($games as $game) {
            $totalMinutes = ($game->sessions_sum_duration_minutes ?? 0) + ($game->manual_minutes ?? 0);
            $sessionCount = $game->sessions()->count();

            foreach ($game->genres as $genre) {
                if (! isset($genreStats[$genre->id])) {
                    $genreStats[$genre->id] = [
                        'name' => $genre->name,
                        'total_minutes' => 0,
                        'total_sessions' => 0,
                        'game_count' => 0,
                    ];
                }
                $genreStats[$genre->id]['total_minutes'] += $totalMinutes;
                $genreStats[$genre->id]['total_sessions'] += $sessionCount;
                $genreStats[$genre->id]['game_count']++;
            }
        }

        return collect($genreStats)
            ->map(function ($stats) {
                $avgSessionMinutes = $stats['total_sessions'] > 0
                    ? $stats['total_minutes'] / $stats['total_sessions']
                    : 0;

                return [
                    'name' => $stats['name'],
                    'hours' => round($stats['total_minutes'] / 60, 1),
                    'sessions' => $stats['total_sessions'],
                    'game_count' => $stats['game_count'],
                    'avg_session_minutes' => round($avgSessionMinutes),
                ];
            })
            ->sortByDesc('hours')
            ->values()
            ->toArray();
    }

    // ========================================
    // Play Patterns Statistics
    // ========================================

    private function getSessionLengthDistribution(): array
    {
        $sessions = PlayStationSession::selectRaw('
            CASE
                WHEN duration_minutes < 15 THEN "micro"
                WHEN duration_minutes < 30 THEN "short"
                WHEN duration_minutes < 60 THEN "medium_short"
                WHEN duration_minutes < 120 THEN "medium"
                WHEN duration_minutes < 240 THEN "long"
                ELSE "marathon"
            END as category,
            COUNT(*) as count,
            SUM(duration_minutes) as total_minutes
        ')
            ->groupBy('category')
            ->get()
            ->keyBy('category');

        $categories = [
            'micro' => ['label' => '< 15 min', 'description' => 'Quick sessions'],
            'short' => ['label' => '15-30 min', 'description' => 'Short sessions'],
            'medium_short' => ['label' => '30-60 min', 'description' => 'Medium sessions'],
            'medium' => ['label' => '1-2 hours', 'description' => 'Standard sessions'],
            'long' => ['label' => '2-4 hours', 'description' => 'Long sessions'],
            'marathon' => ['label' => '4+ hours', 'description' => 'Marathon sessions'],
        ];

        $total = $sessions->sum('count');

        return collect($categories)->map(function ($info, $key) use ($sessions, $total) {
            $data = $sessions[$key] ?? null;

            return [
                'category' => $key,
                'label' => $info['label'],
                'description' => $info['description'],
                'count' => $data->count ?? 0,
                'hours' => round(($data->total_minutes ?? 0) / 60, 1),
                'percentage' => $total > 0 ? round((($data->count ?? 0) / $total) * 100, 1) : 0,
            ];
        })->values()->toArray();
    }

    private function getMarathonSessions(int $limit = 5): array
    {
        return PlayStationSession::with('game')
            ->where('duration_minutes', '>=', 240) // 4+ hours
            ->orderByDesc('duration_minutes')
            ->limit($limit)
            ->get()
            ->map(fn ($session) => [
                'game_name' => $session->game?->name ?? 'Unknown',
                'platform' => $session->game?->platform,
                'duration_minutes' => $session->duration_minutes,
                'hours' => floor($session->duration_minutes / 60),
                'minutes' => $session->duration_minutes % 60,
                'date' => $session->started_at,
                'day_name' => $session->started_at->format('l'),
            ])
            ->toArray();
    }

    private function getMicroSessions(): array
    {
        $microCount = PlayStationSession::where('duration_minutes', '<', 15)->count();
        $totalCount = PlayStationSession::count();

        $topMicroGame = PlayStationSession::with('game')
            ->selectRaw('play_station_game_id, COUNT(*) as count')
            ->where('duration_minutes', '<', 15)
            ->groupBy('play_station_game_id')
            ->orderByDesc('count')
            ->first();

        return [
            'count' => $microCount,
            'percentage' => $totalCount > 0 ? round(($microCount / $totalCount) * 100, 1) : 0,
            'top_game' => $topMicroGame?->game?->name,
            'top_game_count' => $topMicroGame?->count ?? 0,
        ];
    }

    private function getGamingHeatmap(): array
    {
        $data = PlayStationSession::selectRaw('WEEKDAY(started_at) as day, HOUR(started_at) as hour, COUNT(*) as count, SUM(duration_minutes) as minutes')
            ->groupBy('day', 'hour')
            ->get();

        $heatmap = [];
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        foreach ($days as $dayIndex => $dayName) {
            $heatmap[$dayName] = [];
            for ($hour = 0; $hour < 24; $hour++) {
                $heatmap[$dayName][$hour] = 0;
            }
        }

        $maxCount = 0;
        foreach ($data as $row) {
            $dayName = $days[$row->day];
            $heatmap[$dayName][$row->hour] = $row->count;
            if ($row->count > $maxCount) {
                $maxCount = $row->count;
            }
        }

        return [
            'data' => $heatmap,
            'max_count' => $maxCount,
            'days' => $days,
        ];
    }

    private function getBiggestGamingDay(): ?array
    {
        $day = PlayStationSession::selectRaw('DATE(started_at) as date, COUNT(*) as sessions, SUM(duration_minutes) as minutes')
            ->groupBy('date')
            ->orderByDesc('minutes')
            ->first();

        if (! $day) {
            return null;
        }

        $games = PlayStationSession::with('game')
            ->whereDate('started_at', $day->date)
            ->get()
            ->groupBy('play_station_game_id')
            ->map(fn ($sessions) => [
                'name' => $sessions->first()->game?->name ?? 'Unknown',
                'minutes' => $sessions->sum('duration_minutes'),
            ])
            ->sortByDesc('minutes')
            ->values()
            ->toArray();

        return [
            'date' => Carbon::parse($day->date),
            'sessions' => $day->sessions,
            'hours' => round($day->minutes / 60, 1),
            'minutes' => $day->minutes,
            'games' => array_slice($games, 0, 3),
        ];
    }

    private function getMostActiveMonth(): ?array
    {
        $month = PlayStationSession::selectRaw('YEAR(started_at) as year, MONTH(started_at) as month, COUNT(*) as sessions, SUM(duration_minutes) as minutes')
            ->groupBy('year', 'month')
            ->orderByDesc('minutes')
            ->first();

        if (! $month) {
            return null;
        }

        return [
            'year' => $month->year,
            'month' => $month->month,
            'month_name' => Carbon::createFromDate($month->year, $month->month, 1)->format('F Y'),
            'sessions' => $month->sessions,
            'hours' => round($month->minutes / 60, 1),
        ];
    }

    private function getDaysSinceLastSession(): int
    {
        $lastSession = PlayStationSession::orderByDesc('started_at')->first();

        if (! $lastSession) {
            return 0;
        }

        return $lastSession->started_at->diffInDays(now());
    }

    private function getAvgGamesPerMonth(): float
    {
        $firstSession = PlayStationSession::orderBy('started_at')->first();

        if (! $firstSession) {
            return 0;
        }

        $months = $firstSession->started_at->diffInMonths(now()) + 1;
        $uniqueGamesPlayed = PlayStationSession::distinct('play_station_game_id')->count();

        return round($uniqueGamesPlayed / max(1, $months), 1);
    }

    // ========================================
    // Comparisons & Trends
    // ========================================

    private function getMonthComparison(): array
    {
        $thisMonth = PlayStationSession::whereYear('started_at', now()->year)
            ->whereMonth('started_at', now()->month)
            ->selectRaw('COUNT(*) as sessions, SUM(duration_minutes) as minutes')
            ->first();

        $lastMonth = PlayStationSession::whereYear('started_at', now()->subMonth()->year)
            ->whereMonth('started_at', now()->subMonth()->month)
            ->selectRaw('COUNT(*) as sessions, SUM(duration_minutes) as minutes')
            ->first();

        $thisMonthHours = round(($thisMonth->minutes ?? 0) / 60, 1);
        $lastMonthHours = round(($lastMonth->minutes ?? 0) / 60, 1);

        $hoursDiff = $thisMonthHours - $lastMonthHours;
        $percentChange = $lastMonthHours > 0 ? round(($hoursDiff / $lastMonthHours) * 100, 1) : 0;

        return [
            'this_month' => [
                'name' => now()->format('F'),
                'sessions' => $thisMonth->sessions ?? 0,
                'hours' => $thisMonthHours,
            ],
            'last_month' => [
                'name' => now()->subMonth()->format('F'),
                'sessions' => $lastMonth->sessions ?? 0,
                'hours' => $lastMonthHours,
            ],
            'hours_diff' => $hoursDiff,
            'percent_change' => $percentChange,
            'trend' => $hoursDiff > 0 ? 'up' : ($hoursDiff < 0 ? 'down' : 'same'),
        ];
    }

    private function getLibraryGrowth(): array
    {
        return PlayStationGame::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => [
                'year' => $row->year,
                'month' => $row->month,
                'month_name' => Carbon::createFromDate($row->year, $row->month, 1)->format('M Y'),
                'count' => $row->count,
            ])
            ->toArray();
    }

    // ========================================
    // Fun/Trivia Statistics
    // ========================================

    private function getFirstGamePlayed(): ?array
    {
        $session = PlayStationSession::with('game')
            ->orderBy('started_at')
            ->first();

        if (! $session) {
            return null;
        }

        return [
            'name' => $session->game?->name ?? 'Unknown',
            'platform' => $session->game?->platform,
            'date' => $session->started_at,
            'image_url' => $session->game?->image_url,
        ];
    }

    private function getLongestAgoPlayed(): ?array
    {
        $game = PlayStationGame::whereNotNull('last_played_at')
            ->orderBy('last_played_at')
            ->first();

        if (! $game) {
            return null;
        }

        return [
            'name' => $game->name,
            'platform' => $game->platform,
            'last_played_at' => $game->last_played_at,
            'days_ago' => $game->last_played_at->diffInDays(now()),
            'image_url' => $game->image_url,
        ];
    }

    private function getOneHitWonders(int $limit = 5): array
    {
        return PlayStationGame::withCount('sessions')
            ->get()
            ->filter(fn ($game) => $game->sessions_count === 1)
            ->sortByDesc('last_played_at')
            ->take($limit)
            ->map(fn ($game) => [
                'name' => $game->name,
                'platform' => $game->platform,
                'last_played_at' => $game->last_played_at,
                'image_url' => $game->image_url,
            ])
            ->values()
            ->toArray();
    }

    private function getLoyalGames(int $limit = 5): array
    {
        $games = PlayStationGame::withCount('sessions as session_count')
            ->get()
            ->filter(fn ($game) => $game->session_count >= 5);

        return $games->map(function ($game) {
            $gameSessions = $game->sessions()->get();
            $years = $gameSessions
                ->map(fn ($s) => $s->started_at->year)
                ->unique()
                ->sort()
                ->values();

            return [
                'name' => $game->name,
                'platform' => $game->platform,
                'years_played' => $years->count(),
                'years' => $years->toArray(),
                'sessions' => $game->session_count,
                'image_url' => $game->image_url,
            ];
        })
            ->filter(fn ($game) => $game['years_played'] >= 2)
            ->sortByDesc('years_played')
            ->take($limit)
            ->values()
            ->toArray();
    }

    private function getSeasonalPatterns(): array
    {
        $seasons = [
            'Winter' => [12, 1, 2],
            'Spring' => [3, 4, 5],
            'Summer' => [6, 7, 8],
            'Autumn' => [9, 10, 11],
        ];

        $data = PlayStationSession::selectRaw('MONTH(started_at) as month, COUNT(*) as sessions, SUM(duration_minutes) as minutes')
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $seasonStats = [];
        foreach ($seasons as $season => $months) {
            $totalSessions = 0;
            $totalMinutes = 0;

            foreach ($months as $month) {
                $totalSessions += $data[$month]->sessions ?? 0;
                $totalMinutes += $data[$month]->minutes ?? 0;
            }

            $seasonStats[$season] = [
                'sessions' => $totalSessions,
                'hours' => round($totalMinutes / 60, 1),
            ];
        }

        $maxHours = max(array_column($seasonStats, 'hours'));
        $favoriteSeason = array_search($maxHours, array_column($seasonStats, 'hours'));
        $favoriteSeasonName = array_keys($seasonStats)[$favoriteSeason] ?? null;

        return [
            'seasons' => $seasonStats,
            'favorite_season' => $favoriteSeasonName,
            'favorite_hours' => $maxHours,
        ];
    }

    private function getGameHoppingRate(): array
    {
        // Calculate how often you switch games within a day
        $days = PlayStationSession::selectRaw('DATE(started_at) as date')
            ->groupBy('date')
            ->get()
            ->count();

        $gameChanges = 0;
        $totalSessions = 0;

        $sessionsByDay = PlayStationSession::orderBy('started_at')
            ->get()
            ->groupBy(fn ($s) => $s->started_at->format('Y-m-d'));

        foreach ($sessionsByDay as $daySessions) {
            $previousGameId = null;
            foreach ($daySessions->sortBy('started_at') as $session) {
                $totalSessions++;
                if ($previousGameId !== null && $previousGameId !== $session->play_station_game_id) {
                    $gameChanges++;
                }
                $previousGameId = $session->play_station_game_id;
            }
        }

        $hoppingRate = $totalSessions > 1 ? round(($gameChanges / ($totalSessions - $days)) * 100, 1) : 0;

        // Determine player type based on hopping rate
        $playerType = match (true) {
            $hoppingRate < 10 => 'Focused Player',
            $hoppingRate < 25 => 'Balanced Player',
            $hoppingRate < 50 => 'Variety Seeker',
            default => 'Game Hopper',
        };

        return [
            'rate' => max(0, $hoppingRate),
            'game_changes' => $gameChanges,
            'total_sessions' => $totalSessions,
            'player_type' => $playerType,
        ];
    }
}
