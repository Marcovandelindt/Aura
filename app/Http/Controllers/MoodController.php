<?php

namespace App\Http\Controllers;

use App\Models\Mood;
use App\Models\PlayedTrack;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MoodController extends Controller
{
    /**
     * Get all active moods
     */
    public function index(): JsonResponse
    {
        $moods = Mood::active()->popular()->get();

        return response()->json([
            'success' => true,
            'moods' => $moods,
        ]);
    }

    /**
     * Get moods for a specific track
     */
    public function getTrackMoods(Request $request): JsonResponse
    {
        $spotifyTrackId = $request->input('spotify_track_id');

        if (! $spotifyTrackId) {
            return response()->json([
                'success' => false,
                'message' => 'Spotify track ID is required',
            ], 400);
        }

        $trackMoods = PlayedTrack::getMoodsForTrack($spotifyTrackId);
        $allMoods = Mood::active()->popular()->get();

        return response()->json([
            'success' => true,
            'track_moods' => $trackMoods,
            'all_moods' => $allMoods,
            'spotify_track_id' => $spotifyTrackId,
        ]);
    }

    /**
     * Add mood to track
     */
    public function addMoodToTrack(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'spotify_track_id' => 'required|string|max:50',
            'mood_id' => 'required|exists:moods,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data provided',
                'errors' => $validator->errors(),
            ], 400);
        }

        $spotifyTrackId = $request->input('spotify_track_id');
        $moodId = $request->input('mood_id');

        try {
            // Insert the relationship (will ignore if already exists due to unique constraint)
            DB::table('played_track_mood')->insertOrIgnore([
                'spotify_track_id' => $spotifyTrackId,
                'mood_id' => $moodId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Increment mood usage count
            $mood = Mood::find($moodId);
            $mood->incrementUsage();

            return response()->json([
                'success' => true,
                'message' => 'Mood added to track successfully',
                'mood' => $mood,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add mood to track: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove mood from track
     */
    public function removeMoodFromTrack(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'spotify_track_id' => 'required|string|max:50',
            'mood_id' => 'required|exists:moods,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data provided',
                'errors' => $validator->errors(),
            ], 400);
        }

        $spotifyTrackId = $request->input('spotify_track_id');
        $moodId = $request->input('mood_id');

        try {
            $deleted = DB::table('played_track_mood')
                ->where('spotify_track_id', $spotifyTrackId)
                ->where('mood_id', $moodId)
                ->delete();

            if ($deleted) {
                // Decrement mood usage count
                $mood = Mood::find($moodId);
                $mood->decrement('usage_count');

                return response()->json([
                    'success' => true,
                    'message' => 'Mood removed from track successfully',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Mood was not associated with this track',
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove mood from track: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new mood
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:moods,name',
            'color' => ['required', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'icon' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data provided',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $mood = Mood::create($request->only(['name', 'color', 'icon', 'description']));

            return response()->json([
                'success' => true,
                'message' => 'Mood created successfully',
                'mood' => $mood,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create mood: '.$e->getMessage(),
            ], 500);
        }
    }
}
