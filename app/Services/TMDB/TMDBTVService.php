<?php

namespace App\Services\TMDB;

use App\Models\TvEpisode;
use App\Models\TvSeason;
use App\Models\TvSeries;
use Chiiya\Tmdb\Query\AppendToResponse;
use Chiiya\Tmdb\Repositories\SearchRepository;
use Chiiya\Tmdb\Repositories\TvSeasonRepository;
use Chiiya\Tmdb\Repositories\TvShowRepository;
use Illuminate\Support\Facades\Cache;

class TMDBTVService
{
    public function __construct(
        protected TvShowRepository $tvShowRepository,
        protected TvSeasonRepository $tvSeasonRepository,
        protected SearchRepository $searchRepository
    ) {}

    public function search(string $query, int $page = 1): array
    {
        $cacheKey = "tmdb_tv_search_{$query}_{$page}";

        return Cache::remember($cacheKey, config('tmdb.cache_duration'), function () use ($query, $page) {
            $response = $this->searchRepository->searchTv($query, [
                'language' => config('tmdb.language'),
                'page' => $page,
            ]);

            return [
                'results' => collect($response->results)->map(fn ($show) => [
                    'id' => $show->id,
                    'name' => $show->name,
                    'poster_path' => $show->poster_path,
                    'first_air_date' => $show->first_air_date?->format('Y-m-d'),
                    'overview' => $show->overview,
                ])->toArray(),
                'page' => $response->page,
                'total_pages' => $response->total_pages,
                'total_results' => $response->total_results,
            ];
        });
    }

    public function getDetails(int $tmdbId): array
    {
        $cacheKey = "tmdb_tv_details_{$tmdbId}";

        return Cache::remember($cacheKey, config('tmdb.cache_duration'), function () use ($tmdbId) {
            $result = $this->tvShowRepository->getTvShow($tmdbId, [
                'language' => config('tmdb.language'),
                'append_to_response' => new AppendToResponse(['credits', 'videos']),
            ]);

            return json_decode(json_encode($result), true);
        });
    }

    public function getSeasonDetails(int $seriesTmdbId, int $seasonNumber): array
    {
        $cacheKey = "tmdb_tv_season_{$seriesTmdbId}_{$seasonNumber}";

        return Cache::remember($cacheKey, config('tmdb.cache_duration'), function () use ($seriesTmdbId, $seasonNumber) {
            $result = $this->tvSeasonRepository->getTvSeason($seriesTmdbId, $seasonNumber, [
                'language' => config('tmdb.language'),
            ]);

            return json_decode(json_encode($result), true);
        });
    }

    public function createFromTMDB(int $tmdbId): TvSeries
    {
        $details = $this->getDetails($tmdbId);

        $series = TvSeries::updateOrCreate(
            ['tmdb_id' => $tmdbId],
            [
                'name' => $details['name'],
                'original_name' => $details['original_name'] ?? null,
                'overview' => $details['overview'] ?? null,
                'poster_path' => $details['poster_path'] ?? null,
                'backdrop_path' => $details['backdrop_path'] ?? null,
                'first_air_date' => isset($details['first_air_date']) && is_string($details['first_air_date'])
                    ? $details['first_air_date']
                    : (isset($details['first_air_date']['date']) ? $details['first_air_date']['date'] : null),
                'last_air_date' => isset($details['last_air_date']) && is_string($details['last_air_date'])
                    ? $details['last_air_date']
                    : (isset($details['last_air_date']['date']) ? $details['last_air_date']['date'] : null),
                'vote_average' => $details['vote_average'] ?? null,
                'vote_count' => $details['vote_count'] ?? null,
                'genres' => collect($details['genres'] ?? [])->pluck('name')->toArray(),
                'status' => $details['status'] ?? null,
                'original_language' => $details['original_language'] ?? null,
                'number_of_seasons' => $details['number_of_seasons'] ?? 0,
                'number_of_episodes' => $details['number_of_episodes'] ?? 0,
            ]
        );

        return $series;
    }

    public function createSeasonFromTMDB(TvSeries $series, int $seasonNumber): TvSeason
    {
        $details = $this->getSeasonDetails($series->tmdb_id, $seasonNumber);

        $season = TvSeason::updateOrCreate(
            [
                'tv_series_id' => $series->id,
                'season_number' => $seasonNumber,
            ],
            [
                'tmdb_id' => $details['id'],
                'name' => $details['name'],
                'overview' => $details['overview'] ?? null,
                'poster_path' => $details['poster_path'] ?? null,
                'air_date' => isset($details['air_date']) && is_string($details['air_date'])
                    ? $details['air_date']
                    : (isset($details['air_date']['date']) ? $details['air_date']['date'] : null),
                'episode_count' => count($details['episodes'] ?? []),
            ]
        );

        foreach ($details['episodes'] ?? [] as $episodeData) {
            TvEpisode::updateOrCreate(
                [
                    'tv_season_id' => $season->id,
                    'episode_number' => $episodeData['episode_number'],
                ],
                [
                    'tmdb_id' => $episodeData['id'],
                    'name' => $episodeData['name'],
                    'overview' => $episodeData['overview'] ?? null,
                    'still_path' => $episodeData['still_path'] ?? null,
                    'air_date' => isset($episodeData['air_date']) && is_string($episodeData['air_date'])
                        ? $episodeData['air_date']
                        : (isset($episodeData['air_date']['date']) ? $episodeData['air_date']['date'] : null),
                    'runtime' => $episodeData['runtime'] ?? null,
                    'vote_average' => $episodeData['vote_average'] ?? null,
                    'vote_count' => $episodeData['vote_count'] ?? null,
                ]
            );
        }

        return $season;
    }

    public function getPopular(int $page = 1): array
    {
        $cacheKey = "tmdb_tv_popular_{$page}";

        return Cache::remember($cacheKey, config('tmdb.cache_duration'), function () use ($page) {
            $result = $this->tvShowRepository->getPopular([
                'language' => config('tmdb.language'),
                'page' => $page,
            ]);

            return json_decode(json_encode($result), true);
        });
    }

    public function getAiringToday(int $page = 1): array
    {
        $cacheKey = "tmdb_tv_airing_today_{$page}";

        return Cache::remember($cacheKey, config('tmdb.cache_duration'), function () use ($page) {
            $result = $this->tvShowRepository->getAiringToday([
                'language' => config('tmdb.language'),
                'page' => $page,
            ]);

            return json_decode(json_encode($result), true);
        });
    }
}
