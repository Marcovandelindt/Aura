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

        return view('playstation.sessions', compact('sessions', 'games', 'stats', 'search', 'gameId'));
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
