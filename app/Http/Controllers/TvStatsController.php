<?php

namespace App\Http\Controllers;

use App\Models\EpisodeWatch;
use App\Models\TvSeries;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TvStatsController extends Controller
{
    public function index(Request $request): View
    {
        $stats = [
            'overview' => $this->getOverviewStats(),
            'top_series' => $this->getTopSeries(),
            'recently_watched' => $this->getRecentlyWatched(),
            'watch_history' => $this->getWatchHistory(),
            'genre_stats' => $this->getGenreStats(),
            'completion_stats' => $this->getCompletionStats(),
        ];

        return view('tv.stats', compact('stats'));
    }

    private function getOverviewStats(): array
    {
        $totalSeries = TvSeries::count();
        $totalEpisodesWatched = TvSeries::sum('episodes_watched');
        $totalEpisodes = TvSeries::sum('number_of_episodes');

        // Calculate total watched time (sum of runtime for all watched episodes)
        $totalWatchedMinutes = EpisodeWatch::query()
            ->with('episode')
            ->get()
            ->sum(fn ($watch) => $watch->episode->runtime ?? 0);

        $firstWatch = EpisodeWatch::with('episode.season.series')
            ->whereNotNull('watched_at')
            ->orderBy('watched_at')
            ->first();

        $lastWatch = EpisodeWatch::with('episode.season.series')
            ->whereNotNull('watched_at')
            ->orderByDesc('watched_at')
            ->first();

        return [
            'total_series' => $totalSeries,
            'total_episodes_watched' => $totalEpisodesWatched,
            'total_episodes' => $totalEpisodes,
            'total_hours' => round($totalWatchedMinutes / 60, 1),
            'completion_percentage' => $totalEpisodes > 0 ? round(($totalEpisodesWatched / $totalEpisodes) * 100, 1) : 0,
            'first_watch_date' => $firstWatch?->watched_at,
            'last_watch_date' => $lastWatch?->watched_at,
            'first_watch' => $firstWatch ? [
                'series_id' => $firstWatch->episode->season->series->id,
                'series_name' => $firstWatch->episode->season->series->name,
                'episode_name' => $firstWatch->episode->name,
                'season_number' => $firstWatch->episode->season->season_number,
                'episode_number' => $firstWatch->episode->episode_number,
                'watched_at' => $firstWatch->watched_at,
                'poster_url' => $firstWatch->episode->season->series->poster_url,
            ] : null,
            'last_watch' => $lastWatch ? [
                'series_id' => $lastWatch->episode->season->series->id,
                'series_name' => $lastWatch->episode->season->series->name,
                'episode_name' => $lastWatch->episode->name,
                'season_number' => $lastWatch->episode->season->season_number,
                'episode_number' => $lastWatch->episode->episode_number,
                'watched_at' => $lastWatch->watched_at,
                'poster_url' => $lastWatch->episode->season->series->poster_url,
            ] : null,
            'fully_completed' => TvSeries::where('completion_percentage', 100)->count(),
            'in_progress' => TvSeries::where('episodes_watched', '>', 0)->where('completion_percentage', '<', 100)->count(),
        ];
    }

    private function getTopSeries(int $limit = 10): array
    {
        return TvSeries::where('episodes_watched', '>', 0)
            ->with('seasons.episodes.watches')
            ->get()
            ->map(function ($series) {
                $episodes = $series->seasons->flatMap(fn ($season) => $season->episodes);
                $totalWatches = $episodes->sum(fn ($episode) => $episode->watches->count());

                // Calculate total watch time (runtime * number of watches per episode)
                $totalMinutes = $episodes->sum(fn ($episode) => ($episode->runtime ?? 0) * $episode->watches->count());

                return [
                    'id' => $series->id,
                    'name' => $series->name,
                    'episodes_watched' => $series->episodes_watched,
                    'number_of_episodes' => $series->number_of_episodes,
                    'completion_percentage' => $series->completion_percentage,
                    'poster_url' => $series->poster_url,
                    'last_watched_at' => $series->last_watched_at,
                    'vote_average' => $series->vote_average,
                    'total_watches' => $totalWatches,
                    'total_hours' => round($totalMinutes / 60, 1),
                    'total_minutes' => $totalMinutes,
                ];
            })
            ->sortByDesc('total_minutes')
            ->take($limit)
            ->values()
            ->toArray();
    }

    private function getRecentlyWatched(int $limit = 10): array
    {
        $watches = EpisodeWatch::with('episode.season.series')
            ->whereNotNull('watched_at')
            ->orderByDesc('watched_at')
            ->limit($limit)
            ->get()
            ->map(fn ($watch) => [
                'series_id' => $watch->episode->season->series->id,
                'series_name' => $watch->episode->season->series->name,
                'episode_name' => $watch->episode->name,
                'season_number' => $watch->episode->season->season_number,
                'episode_number' => $watch->episode->episode_number,
                'watched_at' => $watch->watched_at,
                'poster_url' => $watch->episode->season->series->poster_url,
            ])
            ->toArray();

        return $watches;
    }

    private function getWatchHistory(): array
    {
        return EpisodeWatch::with('episode.season.series')
            ->whereNotNull('watched_at')
            ->orderByDesc('watched_at')
            ->get()
            ->groupBy(fn ($watch) => $watch->watched_at->format('Y-m-d'))
            ->map(function ($watches, $date) {
                return [
                    'date' => $date,
                    'date_formatted' => Carbon::parse($date)->format('D d M Y'),
                    'count' => $watches->count(),
                    'episodes' => $watches->map(fn ($w) => sprintf(
                        '%s S%02dE%02d',
                        $w->episode->season->series->name,
                        $w->episode->season->season_number,
                        $w->episode->episode_number
                    ))->toArray(),
                ];
            })
            ->values()
            ->toArray();
    }

    private function getGenreStats(): array
    {
        $series = TvSeries::whereNotNull('genres')->get();

        $genreCounts = [];
        foreach ($series as $show) {
            foreach ($show->genres ?? [] as $genre) {
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

    private function getCompletionStats(): array
    {
        return TvSeries::where('episodes_watched', '>', 0)
            ->orderByDesc('completion_percentage')
            ->limit(10)
            ->get()
            ->map(fn ($series) => [
                'name' => $series->name,
                'completion_percentage' => $series->completion_percentage,
                'episodes_watched' => $series->episodes_watched,
                'number_of_episodes' => $series->number_of_episodes,
            ])
            ->toArray();
    }
}
