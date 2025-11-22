<?php

namespace App\Http\Controllers;

use App\Models\EpisodeWatch;
use App\Models\TvEpisode;
use App\Models\TvSeries;
use App\Services\TMDB\TMDBTVService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TvSeriesController extends Controller
{
    public function __construct(
        protected TMDBTVService $tvService
    ) {}

    public function index(): View
    {
        $series = TvSeries::query()
            ->orderBy('last_watched_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('tv.index', compact('series'));
    }

    public function show(TvSeries $series): View
    {
        $series->load(['seasons.episodes']);

        return view('tv.show', compact('series'));
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->input('query');

        if (! $query) {
            return response()->json([
                'success' => false,
                'message' => 'Query is required',
            ], 400);
        }

        try {
            $results = $this->tvService->search($query);

            return response()->json([
                'success' => true,
                'results' => $results['results'] ?? [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search TV series: '.$e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $tmdbId = $request->input('tmdb_id');

        if (! $tmdbId) {
            return response()->json([
                'success' => false,
                'message' => 'TMDB ID is required',
            ], 400);
        }

        try {
            $series = $this->tvService->createFromTMDB($tmdbId);

            for ($seasonNumber = 1; $seasonNumber <= $series->number_of_seasons; $seasonNumber++) {
                $this->tvService->createSeasonFromTMDB($series, $seasonNumber);
            }

            return response()->json([
                'success' => true,
                'message' => 'TV series added successfully',
                'series' => $series->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add TV series: '.$e->getMessage(),
            ], 500);
        }
    }

    public function addEpisodeWatch(Request $request, TvEpisode $episode): JsonResponse
    {
        try {
            EpisodeWatch::create([
                'tv_episode_id' => $episode->id,
                'watched_at' => $request->input('watched_at'),
            ]);

            $episode->season->updateProgress();
            $episode->season->series->recordWatch();

            return response()->json([
                'success' => true,
                'message' => 'Watch added successfully',
                'episode' => $episode->fresh(),
                'series' => $episode->season->series->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add watch: '.$e->getMessage(),
            ], 500);
        }
    }

    public function deleteWatch(EpisodeWatch $watch): JsonResponse
    {
        try {
            $episode = $watch->episode;
            $watch->delete();

            // Update progress
            $episode->season->updateProgress();
            $episode->season->series->updateProgress();

            return response()->json([
                'success' => true,
                'message' => 'Watch deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete watch: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy(TvSeries $series): JsonResponse
    {
        try {
            $series->delete();

            return response()->json([
                'success' => true,
                'message' => 'TV series deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete TV series: '.$e->getMessage(),
            ], 500);
        }
    }
}
