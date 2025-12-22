<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNintendoSwitchGameRequest;
use App\Http\Requests\UpdateNintendoSwitchGameRequest;
use App\Models\Genre;
use App\Models\NintendoSwitchGame;
use App\Models\NintendoSwitchSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class NintendoSwitchController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'hours');

        $query = NintendoSwitchGame::query()
            ->withSum('sessions', 'duration_minutes')
            ->withCount('sessions');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

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

        $stats = [
            'total_games' => NintendoSwitchGame::count(),
            'total_hours' => round(NintendoSwitchSession::sum('duration_minutes') / 60, 1),
            'total_sessions' => NintendoSwitchSession::count(),
            'total_spent' => NintendoSwitchGame::sum('price'),
        ];

        $recentSessions = NintendoSwitchSession::with('game')
            ->orderByDesc('started_at')
            ->limit(10)
            ->get();

        return view('nintendo-switch.index', compact('games', 'stats', 'search', 'sort', 'recentSessions'));
    }

    public function sessions(Request $request): View
    {
        $search = $request->get('search');
        $gameId = $request->get('game');

        $query = NintendoSwitchSession::with('game')
            ->orderByDesc('started_at');

        if ($search) {
            $query->whereHas('game', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($gameId) {
            $query->where('nintendo_switch_game_id', $gameId);
        }

        $sessions = $query->paginate(50)->withQueryString();

        $games = NintendoSwitchGame::orderBy('name')->get(['id', 'name']);

        $stats = [
            'total_sessions' => NintendoSwitchSession::count(),
            'total_minutes' => NintendoSwitchSession::sum('duration_minutes'),
            'this_week' => NintendoSwitchSession::where('started_at', '>=', now()->startOfWeek())->count(),
        ];

        $longestSession = NintendoSwitchSession::with('game')
            ->orderByDesc('duration_minutes')
            ->first();

        $shortestSession = NintendoSwitchSession::with('game')
            ->where('duration_minutes', '>', 0)
            ->orderBy('duration_minutes')
            ->first();

        $longestStreak = $this->calculateLongestStreak();

        return view('nintendo-switch.sessions', compact(
            'sessions',
            'games',
            'stats',
            'search',
            'gameId',
            'longestSession',
            'shortestSession',
            'longestStreak'
        ));
    }

    public function show(NintendoSwitchGame $game): View
    {
        $sessions = NintendoSwitchSession::where('nintendo_switch_game_id', $game->id)
            ->orderByDesc('started_at')
            ->paginate(50);

        $totalMinutes = NintendoSwitchSession::where('nintendo_switch_game_id', $game->id)->sum('duration_minutes');
        $sessionCount = NintendoSwitchSession::where('nintendo_switch_game_id', $game->id)->count();

        $stats = [
            'total_sessions' => $sessionCount,
            'total_hours' => round($totalMinutes / 60, 1),
            'total_minutes' => $totalMinutes,
            'avg_session_minutes' => $sessionCount > 0 ? round($totalMinutes / $sessionCount) : 0,
            'first_played' => NintendoSwitchSession::where('nintendo_switch_game_id', $game->id)->min('started_at'),
            'last_played' => NintendoSwitchSession::where('nintendo_switch_game_id', $game->id)->max('started_at'),
        ];

        $longestSession = NintendoSwitchSession::where('nintendo_switch_game_id', $game->id)
            ->orderByDesc('duration_minutes')
            ->first();

        $shortestSession = NintendoSwitchSession::where('nintendo_switch_game_id', $game->id)
            ->where('duration_minutes', '>', 0)
            ->orderBy('duration_minutes')
            ->first();

        $monthlyStats = NintendoSwitchSession::where('nintendo_switch_game_id', $game->id)
            ->selectRaw('YEAR(started_at) as year, MONTH(started_at) as month, COUNT(*) as sessions, SUM(duration_minutes) as minutes')
            ->groupBy('year', 'month')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->limit(12)
            ->get();

        return view('nintendo-switch.show', compact('game', 'sessions', 'stats', 'longestSession', 'shortestSession', 'monthlyStats'));
    }

    public function create(): View
    {
        return view('nintendo-switch.create');
    }

    public function store(StoreNintendoSwitchGameRequest $request): RedirectResponse
    {
        $data = [
            'name' => $request->name,
            'price' => $request->price,
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('nintendo-switch', 'public');
            $data['image_url'] = Storage::url($path);
        }

        $game = NintendoSwitchGame::create($data);

        return redirect()->route('nintendo-switch.games.show', $game)
            ->with('success', 'Game successfully added.');
    }

    public function edit(NintendoSwitchGame $game): View
    {
        $genres = Genre::orderBy('name')->get();

        return view('nintendo-switch.edit', compact('game', 'genres'));
    }

    public function update(UpdateNintendoSwitchGameRequest $request, NintendoSwitchGame $game): RedirectResponse
    {
        $data = [
            'name' => $request->name,
            'price' => $request->price,
            'play_mode' => $request->play_mode ?: null,
            'main_story_completed' => $request->boolean('main_story_completed'),
            'user_rating' => $request->user_rating,
            'critic_rating' => $request->critic_rating,
        ];

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($game->image_url && str_starts_with($game->image_url, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $game->image_url);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('image')->store('nintendo-switch', 'public');
            $data['image_url'] = Storage::url($path);
        } elseif ($request->boolean('remove_image')) {
            // Delete old image
            if ($game->image_url && str_starts_with($game->image_url, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $game->image_url);
                Storage::disk('public')->delete($oldPath);
            }
            $data['image_url'] = null;
        }

        $game->update($data);

        $game->genres()->sync($request->input('genres', []));

        return redirect()->route('nintendo-switch.games.show', $game)
            ->with('success', 'Game successfully updated.');
    }

    public function destroy(NintendoSwitchGame $game): RedirectResponse
    {
        $game->delete();

        return redirect()->route('nintendo-switch.index')
            ->with('success', 'Game and all sessions deleted.');
    }

    private function calculateLongestStreak(): array
    {
        $sessions = NintendoSwitchSession::with('game')
            ->orderBy('started_at')
            ->get()
            ->groupBy('nintendo_switch_game_id');

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
}
