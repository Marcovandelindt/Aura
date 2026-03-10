<?php

namespace App\Http\Controllers;

use App\Enums\PlayMode;
use App\Models\Genre;
use App\Models\PlayStationGame;
use App\Models\PlayStationSession;
use App\Services\PlayStation\PlayStationScraperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PlayStationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $platform = $request->get('platform');
        $sort = $request->get('sort', 'hours');
        $playtime = $request->get('playtime');

        $query = PlayStationGame::query()
            ->withSum('sessions', 'duration_minutes')
            ->withCount('sessions');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($platform) {
            $query->where('platform', $platform);
        }

        if ($playtime === 'zero') {
            $query->whereDoesntHave('sessions')
                ->where(function ($q) {
                    $q->whereNull('manual_minutes')->orWhere('manual_minutes', 0);
                });
        }

        // Sorting
        $query->orderByDesc(match ($sort) {
            'sessions' => 'sessions_count',
            'last_played' => 'last_played_at',
            'name' => 'name',
            default => 'sessions_sum_duration_minutes',
        });

        if ($sort === 'name') {
            $query->reorder('name', 'asc');
        }

        $games = $query->paginate(25)->withQueryString();

        // Statistics (calculated from sessions + manual minutes)
        $sessionMinutes = PlayStationSession::sum('duration_minutes');
        $manualMinutes = PlayStationGame::sum('manual_minutes') ?? 0;

        $stats = [
            'total_games' => PlayStationGame::count(),
            'total_hours' => round(($sessionMinutes + $manualMinutes) / 60, 1),
            'total_sessions' => PlayStationSession::count(),
            'total_spent' => PlayStationGame::sum('price'),
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

        return view('playstation.index', compact('games', 'stats', 'search', 'platform', 'sort', 'playtime', 'recentSessions'));
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

        // Game-specific stats (calculated from sessions + manual minutes)
        $sessionMinutes = PlayStationSession::where('play_station_game_id', $game->id)->sum('duration_minutes');
        $manualMinutes = $game->manual_minutes ?? 0;
        $totalMinutes = $sessionMinutes + $manualMinutes;
        $sessionCount = PlayStationSession::where('play_station_game_id', $game->id)->count();

        $stats = [
            'total_sessions' => $sessionCount,
            'total_hours' => round($totalMinutes / 60, 1),
            'total_minutes' => $totalMinutes,
            'session_minutes' => $sessionMinutes,
            'manual_minutes' => $manualMinutes,
            'avg_session_minutes' => $sessionCount > 0 ? round($sessionMinutes / $sessionCount) : 0,
            'first_played' => PlayStationSession::where('play_station_game_id', $game->id)->min('started_at'),
            'last_played' => PlayStationSession::where('play_station_game_id', $game->id)->max('started_at'),
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
            ->get();

        return view('playstation.show', compact('game', 'sessions', 'stats', 'longestSession', 'shortestSession', 'monthlyStats'));
    }

    public function create()
    {
        return view('playstation.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'platform' => ['required', 'in:PS5,PS4,PS3,PSVITA'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ]);

        $data = [
            'name' => $request->name,
            'platform' => $request->platform,
            'price' => $request->price,
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('playstation', 'public');
            $data['image_url'] = Storage::url($path);
        }

        $game = PlayStationGame::create($data);

        return redirect()->route('playstation.games.show', $game)
            ->with('success', 'Game added successfully.');
    }

    public function edit(PlayStationGame $game)
    {
        $genres = Genre::orderBy('name')->get();

        return view('playstation.edit', compact('game', 'genres'));
    }

    public function update(Request $request, PlayStationGame $game)
    {
        $request->validate([
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'manual_hours' => ['nullable', 'numeric', 'min:0'],
            'manual_minutes' => ['nullable', 'integer', 'min:0', 'max:59'],
            'play_mode' => ['nullable', Rule::enum(PlayMode::class)],
            'main_story_completed' => ['nullable', 'boolean'],
            'user_rating' => ['nullable', 'integer', 'min:1', 'max:10'],
            'critic_rating' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        // Convert hours and minutes to total minutes
        $totalMinutes = null;
        if ($request->filled('manual_hours') || $request->filled('manual_minutes')) {
            $hours = (int) ($request->manual_hours ?? 0);
            $minutes = (int) ($request->manual_minutes ?? 0);
            $totalMinutes = ($hours * 60) + $minutes;

            // Set to null if total is 0
            if ($totalMinutes === 0) {
                $totalMinutes = null;
            }
        }

        $game->update([
            'price' => $request->price,
            'manual_minutes' => $totalMinutes,
            'play_mode' => $request->play_mode ?: null,
            'main_story_completed' => $request->boolean('main_story_completed'),
            'user_rating' => $request->user_rating,
            'critic_rating' => $request->critic_rating,
        ]);

        $game->genres()->sync($request->input('genres', []));

        return redirect()->route('playstation.games.show', $game)
            ->with('success', 'Game updated successfully.');
    }

    public function convertToManual(PlayStationGame $game)
    {
        // Delete all sessions for this game
        $game->sessions()->delete();

        // Mark as excluded from sync
        $game->update([
            'exclude_from_sync' => true,
            'hours' => null,
            'sessions' => null,
            'avg_session_minutes' => null,
        ]);

        return redirect()->route('playstation.games.edit', $game)
            ->with('success', 'Sessions deleted. You can now set manual playtime.');
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
