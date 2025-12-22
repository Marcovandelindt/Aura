<?php

namespace App\Http\Controllers;

use App\Enums\PlayMode;
use App\Models\Genre;
use App\Models\SteamGame;
use App\Services\Steam\SteamApiService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SteamController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'playtime');
        $filter = $request->get('filter');

        $query = SteamGame::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($filter === 'played') {
            $query->where('playtime_minutes', '>', 0);
        } elseif ($filter === 'never_played') {
            $query->where('playtime_minutes', 0);
        } elseif ($filter === 'recent') {
            $query->whereNotNull('last_played_at')
                ->where('last_played_at', '>=', now()->subDays(30));
        }

        // Sorting
        $query->orderByDesc(match ($sort) {
            'name' => 'name',
            'last_played' => 'last_played_at',
            'recent_playtime' => 'playtime_2weeks_minutes',
            default => 'playtime_minutes',
        });

        if ($sort === 'name') {
            $query->reorder('name', 'asc');
        }

        $games = $query->paginate(25)->withQueryString();

        // Statistics
        $stats = [
            'total_games' => SteamGame::count(),
            'total_hours' => round(SteamGame::sum('playtime_minutes') / 60, 1),
            'played_games' => SteamGame::where('playtime_minutes', '>', 0)->count(),
            'never_played' => SteamGame::where('playtime_minutes', 0)->count(),
            'recent_hours' => round(SteamGame::sum('playtime_2weeks_minutes') / 60, 1),
            'total_spent' => SteamGame::sum('price'),
        ];

        // Most played games
        $mostPlayed = SteamGame::where('playtime_minutes', '>', 0)
            ->orderByDesc('playtime_minutes')
            ->limit(5)
            ->get();

        // Recently played
        $recentlyPlayed = SteamGame::whereNotNull('last_played_at')
            ->orderByDesc('last_played_at')
            ->limit(5)
            ->get();

        return view('steam.index', compact('games', 'stats', 'search', 'sort', 'filter', 'mostPlayed', 'recentlyPlayed'));
    }

    public function show(SteamGame $game)
    {
        return view('steam.show', compact('game'));
    }

    public function edit(SteamGame $game)
    {
        $genres = Genre::orderBy('name')->get();

        return view('steam.edit', compact('game', 'genres'));
    }

    public function update(Request $request, SteamGame $game)
    {
        $request->validate([
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'play_mode' => ['nullable', Rule::enum(PlayMode::class)],
            'main_story_completed' => ['nullable', 'boolean'],
            'user_rating' => ['nullable', 'integer', 'min:1', 'max:10'],
            'critic_rating' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $game->update([
            'price' => $request->price,
            'play_mode' => $request->play_mode ?: null,
            'main_story_completed' => $request->boolean('main_story_completed'),
            'user_rating' => $request->user_rating,
            'critic_rating' => $request->critic_rating,
        ]);

        $game->genres()->sync($request->input('genres', []));

        return redirect()->route('steam.games.show', $game)
            ->with('success', 'Game updated successfully.');
    }

    public function sync(SteamApiService $steamService)
    {
        $result = $steamService->syncGames();

        if ($result['success']) {
            return redirect()->route('steam.index')
                ->with('success', $result['message']);
        }

        return redirect()->route('steam.index')
            ->with('error', 'Sync failed: '.($result['error'] ?? 'Unknown error'));
    }

    public function settings()
    {
        $apiKey = config('services.steam.api_key');
        $steamId = config('services.steam.steam_id');

        return view('steam.settings', compact('apiKey', 'steamId'));
    }

    public function testConnection(SteamApiService $steamService)
    {
        $result = $steamService->testConnection();

        return response()->json($result);
    }
}
