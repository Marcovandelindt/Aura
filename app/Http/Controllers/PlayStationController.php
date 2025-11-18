<?php

namespace App\Http\Controllers;

use App\Models\PlayStationGame;
use App\Models\PlayStationSession;
use App\Services\PlayStation\PlayStationScraperService;
use Illuminate\Http\Request;

class PlayStationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $platform = $request->get('platform');
        $sort = $request->get('sort', 'hours');

        $query = PlayStationGame::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($platform) {
            $query->where('platform', $platform);
        }

        // Sorting
        $query->orderByDesc(match ($sort) {
            'sessions' => 'sessions',
            'last_played' => 'last_played_at',
            'name' => 'name',
            default => 'hours',
        });

        if ($sort === 'name') {
            $query->reorder('name', 'asc');
        }

        $games = $query->paginate(25)->withQueryString();

        // Statistics
        $stats = [
            'total_games' => PlayStationGame::count(),
            'total_hours' => PlayStationGame::sum('hours'),
            'total_sessions' => PlayStationGame::sum('sessions'),
            'platforms' => PlayStationGame::selectRaw('platform, count(*) as count')
                ->groupBy('platform')
                ->pluck('count', 'platform')
                ->toArray(),
        ];

        // Recent sessions
        $recentSessions = PlayStationSession::with('game')
            ->orderByDesc('started_at')
            ->limit(10)
            ->get();

        return view('playstation.index', compact('games', 'stats', 'search', 'platform', 'sort', 'recentSessions'));
    }

    public function sessions(Request $request)
    {
        $search = $request->get('search');
        $gameId = $request->get('game');

        $query = PlayStationSession::with('game')
            ->orderByDesc('started_at');

        if ($search) {
            $query->whereHas('game', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($gameId) {
            $query->where('play_station_game_id', $gameId);
        }

        $sessions = $query->paginate(50)->withQueryString();

        // Get games for filter dropdown
        $games = PlayStationGame::orderBy('name')->get(['id', 'name', 'platform']);

        // Stats
        $stats = [
            'total_sessions' => PlayStationSession::count(),
            'total_minutes' => PlayStationSession::sum('duration_minutes'),
            'this_week' => PlayStationSession::where('started_at', '>=', now()->startOfWeek())->count(),
        ];

        // Longest session
        $longestSession = PlayStationSession::with('game')
            ->orderByDesc('duration_minutes')
            ->first();

        // Shortest session (minimum 1 minute to avoid 0-minute sessions)
        $shortestSession = PlayStationSession::with('game')
            ->where('duration_minutes', '>', 0)
            ->orderBy('duration_minutes')
            ->first();

        // Calculate longest streak for same game
        $longestStreak = $this->calculateLongestStreak();

        // Top 3 most common start hours
        $topStartHours = PlayStationSession::selectRaw('HOUR(started_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderByDesc('count')
            ->limit(3)
            ->get();

        return view('playstation.sessions', compact(
            'sessions',
            'games',
            'stats',
            'search',
            'gameId',
            'longestSession',
            'shortestSession',
            'longestStreak',
            'topStartHours'
        ));
    }

    public function show(PlayStationGame $game)
    {
        $sessions = PlayStationSession::where('play_station_game_id', $game->id)
            ->orderByDesc('started_at')
            ->paginate(50);

        // Game-specific stats
        $stats = [
            'total_sessions' => $game->sessions,
            'total_hours' => $game->hours,
            'total_minutes' => PlayStationSession::where('play_station_game_id', $game->id)->sum('duration_minutes'),
            'avg_session_minutes' => $game->sessions > 0
                ? round(PlayStationSession::where('play_station_game_id', $game->id)->sum('duration_minutes') / $game->sessions)
                : 0,
            'first_played' => PlayStationSession::where('play_station_game_id', $game->id)->min('started_at'),
            'last_played' => $game->last_played_at,
        ];

        // Longest session for this game
        $longestSession = PlayStationSession::where('play_station_game_id', $game->id)
            ->orderByDesc('duration_minutes')
            ->first();

        // Shortest session for this game
        $shortestSession = PlayStationSession::where('play_station_game_id', $game->id)
            ->where('duration_minutes', '>', 0)
            ->orderBy('duration_minutes')
            ->first();

        // Sessions per month
        $monthlyStats = PlayStationSession::where('play_station_game_id', $game->id)
            ->selectRaw('YEAR(started_at) as year, MONTH(started_at) as month, COUNT(*) as sessions, SUM(duration_minutes) as minutes')
            ->groupBy('year', 'month')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->limit(12)
            ->get();

        return view('playstation.show', compact('game', 'sessions', 'stats', 'longestSession', 'shortestSession', 'monthlyStats'));
    }

    private function calculateLongestStreak(): array
    {
        // Get all sessions grouped by game and date
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

        foreach ($sessions as $gameId => $gameSessions) {
            // Get unique dates for this game
            $dates = $gameSessions
                ->map(fn ($s) => $s->started_at->toDateString())
                ->unique()
                ->sort()
                ->values();

            if ($dates->count() < 2) {
                continue;
            }

            // Find longest consecutive sequence
            $currentStreak = 1;
            $maxStreak = 1;
            $streakStart = 0;
            $maxStreakStart = 0;

            for ($i = 1; $i < $dates->count(); $i++) {
                $prevDate = \Carbon\Carbon::parse($dates[$i - 1]);
                $currDate = \Carbon\Carbon::parse($dates[$i]);

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

    public function sync(PlayStationScraperService $scraper)
    {
        $username = config('services.playstation.username', 'Marchoofd');

        $result = $scraper->syncGames($username);

        if ($result['success']) {
            return redirect()->route('playstation.index')
                ->with('success', $result['message']);
        }

        return redirect()->route('playstation.index')
            ->with('error', 'Sync failed: '.($result['error'] ?? 'Unknown error'));
    }
}
