<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\MovieWatch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MovieStatsController extends Controller
{
    public function index(Request $request): View
    {
        $stats = [
            'overview' => $this->getOverviewStats(),
            'top_movies' => $this->getTopMovies(),
            'recently_watched' => $this->getRecentlyWatched(),
            'watch_history' => $this->getWatchHistory(),
            'genre_stats' => $this->getGenreStats(),
        ];

        return view('movies.stats', compact('stats'));
    }

    private function getOverviewStats(): array
    {
        $totalMovies = Movie::count();
        $totalWatches = MovieWatch::count();

        $totalWatchedMinutes = Movie::query()
            ->where('watch_count', '>', 0)
            ->get()
            ->sum(fn ($movie) => $movie->runtime * $movie->watch_count);

        $firstWatch = MovieWatch::with('movie')->whereNotNull('watched_at')->orderBy('watched_at')->first();
        $lastWatch = MovieWatch::with('movie')->whereNotNull('watched_at')->orderByDesc('watched_at')->first();

        return [
            'total_movies' => $totalMovies,
            'total_watches' => $totalWatches,
            'total_hours' => round($totalWatchedMinutes / 60, 1),
            'unique_watched' => Movie::where('watch_count', '>', 0)->count(),
            'first_watch_date' => $firstWatch?->watched_at,
            'last_watch_date' => $lastWatch?->watched_at,
            'first_watch' => $firstWatch ? [
                'movie_id' => $firstWatch->movie->id,
                'movie_title' => $firstWatch->movie->title,
                'watched_at' => $firstWatch->watched_at,
                'poster_url' => $firstWatch->movie->poster_url,
            ] : null,
            'last_watch' => $lastWatch ? [
                'movie_id' => $lastWatch->movie->id,
                'movie_title' => $lastWatch->movie->title,
                'watched_at' => $lastWatch->watched_at,
                'poster_url' => $lastWatch->movie->poster_url,
            ] : null,
            'avg_rating' => round(Movie::where('watch_count', '>', 0)->avg('vote_average'), 1),
        ];
    }

    private function getTopMovies(int $limit = 10): array
    {
        return Movie::where('watch_count', '>', 0)
            ->orderByDesc('watch_count')
            ->limit($limit)
            ->get()
            ->map(fn ($movie) => [
                'id' => $movie->id,
                'title' => $movie->title,
                'watch_count' => $movie->watch_count,
                'runtime' => $movie->runtime,
                'poster_url' => $movie->poster_url,
                'last_watched_at' => $movie->last_watched_at,
                'vote_average' => $movie->vote_average,
            ])
            ->toArray();
    }

    private function getRecentlyWatched(int $limit = 10): array
    {
        return MovieWatch::with('movie')
            ->whereNotNull('watched_at')
            ->orderByDesc('watched_at')
            ->limit($limit)
            ->get()
            ->map(fn ($watch) => [
                'movie_id' => $watch->movie->id,
                'movie_title' => $watch->movie->title,
                'watched_at' => $watch->watched_at,
                'poster_url' => $watch->movie->poster_url,
            ])
            ->toArray();
    }

    private function getWatchHistory(): array
    {
        return MovieWatch::with('movie')
            ->whereNotNull('watched_at')
            ->orderByDesc('watched_at')
            ->get()
            ->groupBy(fn ($watch) => $watch->watched_at->format('Y-m-d'))
            ->map(function ($watches, $date) {
                return [
                    'date' => $date,
                    'date_formatted' => Carbon::parse($date)->format('D d M Y'),
                    'count' => $watches->count(),
                    'movies' => $watches->map(fn ($w) => $w->movie->title)->toArray(),
                ];
            })
            ->values()
            ->toArray();
    }

    private function getGenreStats(): array
    {
        $movies = Movie::whereNotNull('genres')->get();

        $genreCounts = [];
        foreach ($movies as $movie) {
            foreach ($movie->genres ?? [] as $genre) {
                if (! isset($genreCounts[$genre])) {
                    $genreCounts[$genre] = 0;
                }
                $genreCounts[$genre]++;
            }
        }

        arsort($genreCounts);

        return array_map(fn ($genre, $count) => [
            'genre' => $genre,
            'count' => $count,
        ], array_keys($genreCounts), $genreCounts);
    }
}
