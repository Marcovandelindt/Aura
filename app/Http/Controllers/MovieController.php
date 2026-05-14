<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\MovieWatch;
use App\Services\TMDB\TMDBMovieService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MovieController extends Controller
{
    public function __construct(
        protected TMDBMovieService $movieService
    ) {}

    public function index(): View
    {
        $movies = Movie::query()
            ->orderBy('last_watched_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('movies.index', compact('movies'));
    }

    public function show(Movie $movie): View
    {
        $movie->load([
            'watches',
            'people' => fn ($query) => $query->orderByPivot('cast_order'),
        ]);

        return view('movies.show', compact('movie'));
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
            $results = $this->movieService->search($query);

            return response()->json([
                'success' => true,
                'results' => $results['results'] ?? [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search movies: '.$e->getMessage(),
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
            $movie = $this->movieService->createFromTMDB($tmdbId);

            return response()->json([
                'success' => true,
                'message' => 'Movie added successfully',
                'movie' => $movie,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add movie: '.$e->getMessage(),
            ], 500);
        }
    }

    public function markWatched(Request $request, Movie $movie): JsonResponse
    {
        try {
            MovieWatch::create([
                'movie_id' => $movie->id,
                'watched_at' => $request->input('watched_at'),
            ]);

            $movie->incrementWatchCount();

            return response()->json([
                'success' => true,
                'message' => 'Movie marked as watched',
                'movie' => $movie->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark movie as watched: '.$e->getMessage(),
            ], 500);
        }
    }

    public function deleteWatch(MovieWatch $watch): JsonResponse
    {
        try {
            $movie = $watch->movie;
            $watch->delete();

            // Update watch count
            $movie->decrement('watch_count');
            if ($movie->watch_count === 0) {
                $movie->first_watched_at = null;
                $movie->last_watched_at = null;
            } else {
                $latestWatch = $movie->watches()->latest('watched_at')->first();
                $movie->last_watched_at = $latestWatch?->watched_at;
            }
            $movie->save();

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

    public function destroy(Movie $movie): JsonResponse
    {
        try {
            $movie->delete();

            return response()->json([
                'success' => true,
                'message' => 'Movie deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete movie: '.$e->getMessage(),
            ], 500);
        }
    }
}
