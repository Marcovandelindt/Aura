<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
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
            ->with('seasons.episodes.watches')
            ->orderBy('last_watched_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($show) {
                $episodes = $show->seasons->flatMap(fn ($season) => $season->episodes);
                $totalWatches = $episodes->sum(fn ($episode) => $episode->watches->count());
                $totalMinutes = $episodes->sum(fn ($episode) => ($episode->runtime ?? 0) * $episode->watches->count());

                $show->total_watches = $totalWatches;
                $show->total_hours = round($totalMinutes / 60, 1);

                return $show;
            })
            ->sortByDesc('total_watches')
            ->values();

        return view('tv.index', compact('series'));
    }

    public function show(TvSeries $series): View
    {
        $series->load([
            'seasons.episodes',
            'people' => fn ($query) => $query->orderByPivot('cast_order'),
        ]);

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
                'year_only' => $request->boolean('year_only'),
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

    public function bulkWatch(Request $request, TvSeries $series): JsonResponse
    {
        try {
            $watchedAt = $request->input('watched_at');
            $yearOnly = $request->boolean('year_only');
            $count = 0;

            $series->load('seasons.episodes');

            foreach ($series->seasons as $season) {
                foreach ($season->episodes as $episode) {
                    EpisodeWatch::create([
                        'tv_episode_id' => $episode->id,
                        'watched_at' => $watchedAt,
                        'year_only' => $yearOnly,
                    ]);
                    $count++;
                }

                $season->updateProgress();
            }

            $series->updateProgress();
            $series->recordWatch();

            return response()->json([
                'success' => true,
                'count' => $count,
                'message' => "Added {$count} episode watches successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add bulk watches: '.$e->getMessage(),
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

    public function refresh(TvSeries $series): JsonResponse
    {
        try {
            $this->tvService->clearCache($series->tmdb_id);

            $updatedSeries = $this->tvService->createFromTMDB($series->tmdb_id);

            for ($seasonNumber = 1; $seasonNumber <= $updatedSeries->number_of_seasons; $seasonNumber++) {
                $this->tvService->createSeasonFromTMDB($updatedSeries, $seasonNumber);
            }

            $updatedSeries->load('seasons');
            foreach ($updatedSeries->seasons as $season) {
                $season->updateProgress();
            }
            $updatedSeries->updateProgress();

            return response()->json([
                'success' => true,
                'message' => 'TV series refreshed successfully',
                'series' => $updatedSeries->fresh(['seasons.episodes']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh TV series: '.$e->getMessage(),
            ], 500);
        }
    }
}
