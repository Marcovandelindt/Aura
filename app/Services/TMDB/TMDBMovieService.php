<?php

namespace App\Services\TMDB;

use App\Models\Movie;
use Chiiya\Tmdb\Query\AppendToResponse;
use Chiiya\Tmdb\Repositories\MovieRepository;
use Chiiya\Tmdb\Repositories\SearchRepository;
use Illuminate\Support\Facades\Cache;

class TMDBMovieService
{
    public function __construct(
        protected MovieRepository $movieRepository,
        protected SearchRepository $searchRepository
    ) {}

    public function search(string $query, int $page = 1): array
    {
        $cacheKey = "tmdb_movie_search_{$query}_{$page}";

        return Cache::remember($cacheKey, config('tmdb.cache_duration'), function () use ($query, $page) {
            $response = $this->searchRepository->searchMovies($query, [
                'language' => 'en-US',
                'page' => $page,
            ]);

            return [
                'results' => collect($response->results)->map(fn ($movie) => [
                    'id' => $movie->id,
                    'title' => $movie->title,
                    'poster_path' => $movie->poster_path,
                    'release_date' => $movie->release_date?->format('Y-m-d'),
                    'overview' => $movie->overview,
                ])->toArray(),
                'page' => $response->page,
                'total_pages' => $response->total_pages,
                'total_results' => $response->total_results,
            ];
        });
    }

    public function getDetails(int $tmdbId): array
    {
        $cacheKey = "tmdb_movie_details_{$tmdbId}";

        return Cache::remember($cacheKey, config('tmdb.cache_duration'), function () use ($tmdbId) {
            $result = $this->movieRepository->getMovie($tmdbId, [
                'language' => config('tmdb.language'),
                'append_to_response' => new AppendToResponse(['credits', 'videos']),
            ]);

            return json_decode(json_encode($result), true);
        });
    }

    public function createFromTMDB(int $tmdbId): Movie
    {
        $details = $this->getDetails($tmdbId);

        return Movie::updateOrCreate(
            ['tmdb_id' => $tmdbId],
            [
                'title' => $details['title'],
                'original_title' => $details['original_title'] ?? null,
                'overview' => $details['overview'] ?? null,
                'poster_path' => $details['poster_path'] ?? null,
                'backdrop_path' => $details['backdrop_path'] ?? null,
                'release_date' => isset($details['release_date']) && is_string($details['release_date'])
                    ? $details['release_date']
                    : (isset($details['release_date']['date']) ? $details['release_date']['date'] : null),
                'runtime' => $details['runtime'] ?? null,
                'vote_average' => $details['vote_average'] ?? null,
                'vote_count' => $details['vote_count'] ?? null,
                'genres' => collect($details['genres'] ?? [])->pluck('name')->toArray(),
                'status' => $details['status'] ?? null,
                'original_language' => $details['original_language'] ?? null,
            ]
        );
    }

    public function getPopular(int $page = 1): array
    {
        $cacheKey = "tmdb_movies_popular_{$page}";

        return Cache::remember($cacheKey, config('tmdb.cache_duration'), function () use ($page) {
            $result = $this->movieRepository->getPopular([
                'language' => config('tmdb.language'),
                'page' => $page,
                'region' => config('tmdb.region'),
            ]);

            return json_decode(json_encode($result), true);
        });
    }

    public function getNowPlaying(int $page = 1): array
    {
        $cacheKey = "tmdb_movies_now_playing_{$page}";

        return Cache::remember($cacheKey, config('tmdb.cache_duration'), function () use ($page) {
            $result = $this->movieRepository->getNowPlaying([
                'language' => config('tmdb.language'),
                'page' => $page,
                'region' => config('tmdb.region'),
            ]);

            return json_decode(json_encode($result), true);
        });
    }
}
