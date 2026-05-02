<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Mood;
use App\Models\Track;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MoodController extends Controller
{
    public function index(): JsonResponse
    {
        $moods = Mood::active()->popular()->get();

        return response()->json([
            'success' => true,
            'moods' => $moods,
        ]);
    }

    public function getTrackMoods(Request $request): JsonResponse
    {
        $spotifyTrackId = $request->input('spotify_track_id');

        if (! $spotifyTrackId) {
            return response()->json(['success' => false, 'message' => 'Spotify track ID is required'], 400);
        }

        $track = Track::where('spotify_track_id', $spotifyTrackId)->first();

        if (! $track) {
            return response()->json(['success' => true, 'track_moods' => [], 'all_moods' => Mood::active()->popular()->get(), 'all_games' => Game::active()->popular()->get(), 'track_mood_games' => [], 'spotify_track_id' => $spotifyTrackId]);
        }

        $trackMoods = $track->moods()->where('moods.is_active', true)->get();
        $allMoods = Mood::active()->popular()->get();
        $allGames = Game::active()->popular()->get();

        $trackMoodGames = DB::table('track_mood')
            ->where('track_mood.track_id', $track->id)
            ->whereNotNull('game_id')
            ->join('games', 'track_mood.game_id', '=', 'games.id')
            ->select('track_mood.mood_id', 'games.id', 'games.name', 'games.slug')
            ->get()
            ->groupBy('mood_id');

        return response()->json([
            'success' => true,
            'track_moods' => $trackMoods,
            'all_moods' => $allMoods,
            'all_games' => $allGames,
            'track_mood_games' => $trackMoodGames,
            'spotify_track_id' => $spotifyTrackId,
        ]);
    }

    public function addMoodToTrack(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'spotify_track_id' => 'required|string|max:50',
            'mood_id' => 'required|exists:moods,id',
            'game_id' => 'nullable|exists:games,id',
            'game_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid data provided', 'errors' => $validator->errors()], 400);
        }

        $spotifyTrackId = $request->input('spotify_track_id');
        $moodId = $request->input('mood_id');
        $gameId = $request->input('game_id');
        $gameName = $request->input('game_name');

        $track = Track::where('spotify_track_id', $spotifyTrackId)->first();

        if (! $track) {
            return response()->json(['success' => false, 'message' => 'Track not found'], 404);
        }

        try {
            if ($gameName && ! $gameId) {
                $game = Game::firstOrCreate(['name' => $gameName], ['is_active' => true]);
                $gameId = $game->id;
            }

            $existing = DB::table('track_mood')
                ->where('track_id', $track->id)
                ->where('mood_id', $moodId)
                ->first();

            if ($existing) {
                if ($gameId && $existing->game_id != $gameId) {
                    DB::table('track_mood')
                        ->where('track_id', $track->id)
                        ->where('mood_id', $moodId)
                        ->update(['game_id' => $gameId, 'updated_at' => now()]);

                    if ($existing->game_id) {
                        Game::find($existing->game_id)?->decrementTrackCount();
                    }
                    Game::find($gameId)?->incrementTrackCount();
                }
            } else {
                DB::table('track_mood')->insert([
                    'track_id' => $track->id,
                    'mood_id' => $moodId,
                    'game_id' => $gameId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Mood::find($moodId)->incrementUsage();

                if ($gameId) {
                    Game::find($gameId)?->incrementTrackCount();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Mood added to track successfully',
                'mood' => Mood::find($moodId),
                'game' => $gameId ? Game::find($gameId) : null,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to add mood to track: '.$e->getMessage()], 500);
        }
    }

    public function removeMoodFromTrack(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'spotify_track_id' => 'required|string|max:50',
            'mood_id' => 'required|exists:moods,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid data provided', 'errors' => $validator->errors()], 400);
        }

        $spotifyTrackId = $request->input('spotify_track_id');
        $moodId = $request->input('mood_id');

        $track = Track::where('spotify_track_id', $spotifyTrackId)->first();

        if (! $track) {
            return response()->json(['success' => false, 'message' => 'Track not found'], 404);
        }

        try {
            $record = DB::table('track_mood')
                ->where('track_id', $track->id)
                ->where('mood_id', $moodId)
                ->first();

            if ($record) {
                DB::table('track_mood')
                    ->where('track_id', $track->id)
                    ->where('mood_id', $moodId)
                    ->delete();

                Mood::find($moodId)?->decrement('usage_count');

                if ($record->game_id) {
                    Game::find($record->game_id)?->decrementTrackCount();
                }

                return response()->json(['success' => true, 'message' => 'Mood removed from track successfully']);
            } else {
                return response()->json(['success' => false, 'message' => 'Mood was not associated with this track'], 404);
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to remove mood from track: '.$e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:moods,name',
            'color' => ['required', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'icon' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid data provided', 'errors' => $validator->errors()], 400);
        }

        try {
            $mood = Mood::create($request->only(['name', 'color', 'icon', 'description']));

            return response()->json(['success' => true, 'message' => 'Mood created successfully', 'mood' => $mood], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to create mood: '.$e->getMessage()], 500);
        }
    }

    public function getTrackGames(Request $request): JsonResponse
    {
        $spotifyTrackId = $request->input('spotify_track_id');

        if (! $spotifyTrackId) {
            return response()->json(['success' => false, 'message' => 'Spotify track ID is required'], 400);
        }

        $gameMood = Mood::where('name', 'Game')->first();

        if (! $gameMood) {
            return response()->json(['success' => false, 'message' => 'Game mood not found'], 404);
        }

        $track = Track::where('spotify_track_id', $spotifyTrackId)->first();

        if (! $track) {
            return response()->json(['success' => false, 'message' => 'Track not found', 'has_game_mood' => false]);
        }

        $hasGameMood = DB::table('track_mood')
            ->where('track_id', $track->id)
            ->where('mood_id', $gameMood->id)
            ->exists();

        if (! $hasGameMood) {
            return response()->json(['success' => false, 'message' => 'Track does not have Game mood', 'has_game_mood' => false]);
        }

        $assignedGames = DB::table('track_mood')
            ->where('track_id', $track->id)
            ->where('mood_id', $gameMood->id)
            ->whereNotNull('game_id')
            ->join('games', 'track_mood.game_id', '=', 'games.id')
            ->select('games.*')
            ->get();

        return response()->json([
            'success' => true,
            'has_game_mood' => true,
            'assigned_games' => $assignedGames,
            'all_games' => Game::active()->popular()->get(),
            'spotify_track_id' => $spotifyTrackId,
        ]);
    }

    public function assignGameToTrack(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'spotify_track_id' => 'required|string|max:50',
            'game_id' => 'nullable|exists:games,id',
            'game_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid data provided', 'errors' => $validator->errors()], 400);
        }

        $spotifyTrackId = $request->input('spotify_track_id');
        $gameId = $request->input('game_id');
        $gameName = $request->input('game_name');

        try {
            $gameMood = Mood::where('name', 'Game')->first();

            if (! $gameMood) {
                return response()->json(['success' => false, 'message' => 'Game mood not found'], 404);
            }

            $track = Track::where('spotify_track_id', $spotifyTrackId)->first();

            if (! $track) {
                return response()->json(['success' => false, 'message' => 'Track not found'], 404);
            }

            $trackMood = DB::table('track_mood')
                ->where('track_id', $track->id)
                ->where('mood_id', $gameMood->id)
                ->first();

            if (! $trackMood) {
                return response()->json(['success' => false, 'message' => 'Track must have Game mood before assigning a game'], 400);
            }

            if ($gameName && ! $gameId) {
                $game = Game::firstOrCreate(['name' => $gameName], ['is_active' => true]);
                $gameId = $game->id;
            }

            if (! $gameId) {
                return response()->json(['success' => false, 'message' => 'Either game_id or game_name must be provided'], 400);
            }

            $oldGameId = $trackMood->game_id;

            DB::table('track_mood')
                ->where('track_id', $track->id)
                ->where('mood_id', $gameMood->id)
                ->update(['game_id' => $gameId, 'updated_at' => now()]);

            if ($oldGameId && $oldGameId != $gameId) {
                Game::find($oldGameId)?->decrementTrackCount();
            }

            if (! $oldGameId || $oldGameId != $gameId) {
                Game::find($gameId)?->incrementTrackCount();
            }

            return response()->json([
                'success' => true,
                'message' => 'Game assigned successfully',
                'game' => Game::find($gameId),
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to assign game: '.$e->getMessage()], 500);
        }
    }
}
