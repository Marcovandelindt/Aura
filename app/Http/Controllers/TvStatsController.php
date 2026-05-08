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
            'watch_time_chart' => $this->getWatchTimeChart(),
            'heatmap' => $this->getHeatmapData(),
            'hour_distribution' => $this->getHourDistribution(),
            'abandonment' => $this->getAbandonmentData(),
            'completion_time' => $this->getCompletionTimeData(),
            'longest_pause' => $this->getLongestPauseData(),
            'top_series' => $this->getTopSeries(18),
            'recently_watched' => $this->getRecentlyWatched(),
            'watch_history' => $this->getWatchHistory(),
            'genre_stats' => $this->getGenreStats(),
            'completion_stats' => $this->getCompletionStats(),
            'viewing_patterns' => $this->getViewingPatterns(),
            'time_based' => $this->getTimeBasedStats(),
            'content_breakdown' => $this->getContentBreakdown(),
            'personal_records' => $this->getPersonalRecords(),
            'comparisons' => $this->getComparisons(),
        ];

        return view('tv.stats', compact('stats'));
    }

    private function getHeatmapData(): array
    {
        $endDate = Carbon::now()->endOfDay();
        $startDate = Carbon::now()->subDays(364)->startOfDay();

        $counts = EpisodeWatch::whereNotNull('watched_at')
            ->whereBetween('watched_at', [$startDate, $endDate])
            ->get()
            ->groupBy(fn ($w) => $w->watched_at->format('Y-m-d'))
            ->map(fn ($group) => $group->count());

        $result = [];
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $date = $current->format('Y-m-d');
            $result[] = [
                'date' => $date,
                'count' => $counts[$date] ?? 0,
                'day_of_week' => $current->dayOfWeek,
            ];
            $current->addDay();
        }

        return $result;
    }

    private function getHourDistribution(): array
    {
        $hours = array_fill(0, 24, 0);

        EpisodeWatch::whereNotNull('watched_at')->get()
            ->each(function ($watch) use (&$hours) {
                $hours[$watch->watched_at->hour]++;
            });

        return $hours;
    }

    private function getAbandonmentData(): array
    {
        return TvSeries::where('episodes_watched', '>', 0)
            ->where('completion_percentage', '<', 100)
            ->orderByDesc('completion_percentage')
            ->get()
            ->map(fn ($series) => [
                'id' => $series->id,
                'name' => $series->name,
                'completion_percentage' => $series->completion_percentage,
                'episodes_watched' => $series->episodes_watched,
                'number_of_episodes' => $series->number_of_episodes,
                'poster_url' => $series->poster_url,
            ])
            ->toArray();
    }

    private function getCompletionTimeData(): array
    {
        return TvSeries::where('completion_percentage', 100)
            ->with('seasons.episodes.watches')
            ->get()
            ->map(function ($series) {
                $allWatches = $series->seasons
                    ->flatMap(fn ($s) => $s->episodes)
                    ->flatMap(fn ($e) => $e->watches)
                    ->filter(fn ($w) => $w->watched_at !== null)
                    ->sortBy('watched_at');

                if ($allWatches->isEmpty()) {
                    return null;
                }

                $maxPerDay = $allWatches
                    ->groupBy(fn ($w) => $w->watched_at->format('Y-m-d'))
                    ->map(fn ($group) => $group->count())
                    ->max();

                if ($maxPerDay > 20) {
                    return null;
                }

                // Sliding window of size $episodeCount to find the fastest complete run
                $episodeCount = $series->number_of_episodes;
                $dates = $allWatches->sortBy('watched_at')->pluck('watched_at')->values();
                $total = $dates->count();

                if ($total < $episodeCount) {
                    return null;
                }

                $fastestDays = PHP_INT_MAX;
                for ($i = 0; $i <= $total - $episodeCount; $i++) {
                    $span = max(1, $dates[$i]->diffInDays($dates[$i + $episodeCount - 1]) + 1);
                    if ($span < $fastestDays) {
                        $fastestDays = $span;
                    }
                }

                return [
                    'id' => $series->id,
                    'name' => $series->name,
                    'days' => $fastestDays,
                    'episodes' => $episodeCount,
                    'eps_per_day' => round($episodeCount / $fastestDays, 1),
                ];
            })
            ->filter()
            ->sortBy('days')
            ->values()
            ->toArray();
    }

    private function getLongestPauseData(): array
    {
        $watches = EpisodeWatch::with('episode.season.series')
            ->whereNotNull('watched_at')
            ->get()
            ->groupBy(fn ($w) => $w->episode->season->series->id);

        $pauses = [];

        foreach ($watches as $seriesWatches) {
            $sorted = $seriesWatches->sortBy('watched_at')->values();
            $total = $sorted->count();

            if ($total < 2) {
                continue;
            }

            $series = $sorted->first()->episode->season->series;
            $episodeCount = max(1, $series->number_of_episodes);
            $estimatedRuns = max(1, (int) round($total / $episodeCount));

            // Build all consecutive gaps with their index
            $gaps = [];
            for ($i = 1; $i < $total; $i++) {
                $gaps[$i - 1] = $sorted[$i - 1]->watched_at->diffInDays($sorted[$i]->watched_at);
            }

            // Mark the ($estimatedRuns - 1) largest gaps as run boundaries to exclude
            $boundaryIndices = [];
            if ($estimatedRuns > 1) {
                $sortedBySize = $gaps;
                arsort($sortedBySize);
                $boundaryIndices = array_slice(array_keys($sortedBySize), 0, $estimatedRuns - 1);
            }

            $maxDays = 0;
            foreach ($gaps as $idx => $gap) {
                if (! in_array($idx, $boundaryIndices)) {
                    $maxDays = max($maxDays, $gap);
                }
            }

            if ($maxDays > 0) {
                $pauses[] = [
                    'series_name' => $series->name,
                    'days' => $maxDays,
                ];
            }
        }

        usort($pauses, fn ($a, $b) => $b['days'] - $a['days']);

        return array_slice($pauses, 0, 10);
    }

    private function getWatchTimeChart(): array
    {
        $watches = EpisodeWatch::with('episode')
            ->whereNotNull('watched_at')
            ->get();

        if ($watches->isEmpty()) {
            return [];
        }

        $startDate = $watches->min('watched_at')->copy()->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $minutesByDate = $watches
            ->groupBy(fn ($w) => $w->watched_at->format('Y-m-d'))
            ->map(fn ($group) => $group->sum(fn ($w) => $w->episode->runtime ?? 0));

        $result = [];
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $date = $current->format('Y-m-d');
            $result[] = [
                'date' => $date,
                'hours' => round(($minutesByDate[$date] ?? 0) / 60, 2),
            ];
            $current->addDay();
        }

        return $result;
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

    private function getViewingPatterns(): array
    {
        $watches = EpisodeWatch::whereNotNull('watched_at')->get();

        // Most Active Viewing Day
        $dayOfWeekCounts = $watches->groupBy(fn ($w) => $w->watched_at->dayOfWeek)
            ->map(fn ($group) => $group->count())
            ->sortDesc();

        $mostActiveDay = $dayOfWeekCounts->keys()->first();
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        // Binge Sessions (5+ episodes in a day)
        $bingeSessions = $watches->groupBy(fn ($w) => $w->watched_at->format('Y-m-d'))
            ->filter(fn ($group) => $group->count() >= 5)
            ->map(function ($group, $date) {
                return [
                    'date' => Carbon::parse($date)->format('M d, Y'),
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->take(10)
            ->toArray();

        // Longest Streak
        $dates = $watches->pluck('watched_at')->map(fn ($d) => $d->format('Y-m-d'))->unique()->sort()->values();
        $longestStreak = 0;
        $currentStreak = 1;

        for ($i = 1; $i < $dates->count(); $i++) {
            $prevDate = Carbon::parse($dates[$i - 1]);
            $currDate = Carbon::parse($dates[$i]);

            if ($prevDate->diffInDays($currDate) === 1) {
                $currentStreak++;
            } else {
                $longestStreak = max($longestStreak, $currentStreak);
                $currentStreak = 1;
            }
        }
        $longestStreak = max($longestStreak, $currentStreak);

        // Average Episodes Per Day
        $totalDays = $watches->pluck('watched_at')->map(fn ($d) => $d->format('Y-m-d'))->unique()->count();
        $avgEpisodesPerDay = $totalDays > 0 ? round($watches->count() / $totalDays, 1) : 0;

        return [
            'most_active_day' => $mostActiveDay !== null ? $dayNames[$mostActiveDay] : 'N/A',
            'most_active_day_count' => $mostActiveDay !== null ? $dayOfWeekCounts->first() : 0,
            'binge_sessions' => $bingeSessions,
            'longest_streak' => $longestStreak,
            'avg_episodes_per_day' => $avgEpisodesPerDay,
        ];
    }

    private function getTimeBasedStats(): array
    {
        $watches = EpisodeWatch::with('episode')->whereNotNull('watched_at')->get();

        // Busiest Month
        $monthCounts = $watches->groupBy(fn ($w) => $w->watched_at->format('Y-m'))
            ->map(fn ($group) => $group->count())
            ->sortDesc();

        $busiestMonth = $monthCounts->keys()->first();

        // Busiest Year
        $yearCounts = $watches->groupBy(fn ($w) => $w->watched_at->format('Y'))
            ->map(fn ($group) => $group->count())
            ->sortDesc();

        $busiestYear = $yearCounts->keys()->first();

        // Watch Time by Year
        $watchTimeByYear = $watches->groupBy(fn ($w) => $w->watched_at->format('Y'))
            ->map(function ($group) {
                $totalMinutes = $group->sum(fn ($w) => $w->episode->runtime ?? 0);

                return round($totalMinutes / 60, 1);
            })
            ->sortKeys()
            ->toArray();

        // Most Rewatched
        $mostRewatched = EpisodeWatch::with('episode.season.series')
            ->get()
            ->groupBy('tv_episode_id')
            ->filter(fn ($group) => $group->count() > 1)
            ->map(function ($group) {
                $episode = $group->first()->episode;

                return [
                    'series_name' => $episode->season->series->name,
                    'episode_name' => $episode->name,
                    'season_number' => $episode->season->season_number,
                    'episode_number' => $episode->episode_number,
                    'watch_count' => $group->count(),
                ];
            })
            ->sortByDesc('watch_count')
            ->values()
            ->take(10)
            ->toArray();

        // Average Episode Length
        $avgEpisodeLength = $watches->avg(fn ($w) => $w->episode->runtime ?? 0);

        return [
            'busiest_month' => $busiestMonth ? Carbon::parse($busiestMonth.'-01')->format('F Y') : 'N/A',
            'busiest_month_count' => $busiestMonth ? $monthCounts->first() : 0,
            'busiest_year' => $busiestYear ?? 'N/A',
            'busiest_year_count' => $busiestYear ? $yearCounts->first() : 0,
            'watch_time_by_year' => $watchTimeByYear,
            'most_rewatched' => $mostRewatched,
            'avg_episode_length' => round($avgEpisodeLength, 0),
        ];
    }

    private function getContentBreakdown(): array
    {
        $allSeries = TvSeries::all();

        // Completion Rate
        $startedSeries = $allSeries->filter(fn ($s) => $s->episodes_watched > 0)->count();
        $completedSeries = $allSeries->filter(fn ($s) => $s->completion_percentage >= 100)->count();
        $completionRate = $startedSeries > 0 ? round(($completedSeries / $startedSeries) * 100, 1) : 0;

        // Most Popular Decade
        $decadeCounts = $allSeries->filter(fn ($s) => $s->first_air_date)
            ->groupBy(fn ($s) => floor($s->first_air_date->year / 10) * 10)
            ->map(fn ($group) => $group->count())
            ->sortDesc();

        $mostPopularDecade = $decadeCounts->keys()->first();

        // Episode Count Distribution
        $episodeDistribution = [
            '1-10' => $allSeries->filter(fn ($s) => $s->number_of_episodes >= 1 && $s->number_of_episodes <= 10)->count(),
            '11-25' => $allSeries->filter(fn ($s) => $s->number_of_episodes >= 11 && $s->number_of_episodes <= 25)->count(),
            '26-50' => $allSeries->filter(fn ($s) => $s->number_of_episodes >= 26 && $s->number_of_episodes <= 50)->count(),
            '51-100' => $allSeries->filter(fn ($s) => $s->number_of_episodes >= 51 && $s->number_of_episodes <= 100)->count(),
            '100+' => $allSeries->filter(fn ($s) => $s->number_of_episodes > 100)->count(),
        ];

        return [
            'completion_rate' => $completionRate,
            'started_series' => $startedSeries,
            'completed_series' => $completedSeries,
            'most_popular_decade' => $mostPopularDecade ? $mostPopularDecade.'s' : 'N/A',
            'most_popular_decade_count' => $mostPopularDecade ? $decadeCounts->first() : 0,
            'episode_distribution' => $episodeDistribution,
        ];
    }

    private function getPersonalRecords(): array
    {
        $watches = EpisodeWatch::with('episode.season.series')->whereNotNull('watched_at')->get();

        // First & Last Episode of the Year
        $episodesByYear = $watches->groupBy(fn ($w) => $w->watched_at->year)
            ->map(function ($yearWatches) {
                $first = $yearWatches->sortBy('watched_at')->first();
                $last = $yearWatches->sortByDesc('watched_at')->first();

                return [
                    'first' => [
                        'series_name' => $first->episode->season->series->name,
                        'episode_name' => $first->episode->name,
                        'date' => $first->watched_at->format('M d, Y'),
                    ],
                    'last' => [
                        'series_name' => $last->episode->season->series->name,
                        'episode_name' => $last->episode->name,
                        'date' => $last->watched_at->format('M d, Y'),
                    ],
                ];
            })
            ->sortKeysDesc()
            ->toArray();

        // Highest Rated Shows Watched
        $highestRated = TvSeries::where('episodes_watched', '>', 0)
            ->whereNotNull('vote_average')
            ->orderByDesc('vote_average')
            ->limit(10)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'vote_average' => $s->vote_average,
                'poster_url' => $s->poster_url,
            ])
            ->toArray();

        // Longest Series Completed
        $longestCompleted = TvSeries::where('completion_percentage', 100)
            ->orderByDesc('number_of_episodes')
            ->limit(10)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'number_of_episodes' => $s->number_of_episodes,
            ])
            ->toArray();

        // Fastest Binge
        $fastestBinge = TvSeries::where('completion_percentage', 100)
            ->with('seasons.episodes.watches')
            ->get()
            ->map(function ($series) {
                $allWatches = $series->seasons->flatMap(fn ($s) => $s->episodes)
                    ->flatMap(fn ($e) => $e->watches)
                    ->filter(fn ($w) => $w->watched_at !== null);

                if ($allWatches->isEmpty()) {
                    return null;
                }

                $firstWatch = $allWatches->sortBy('watched_at')->first();
                $lastWatch = $allWatches->sortByDesc('watched_at')->first();

                $days = $firstWatch->watched_at->diffInDays($lastWatch->watched_at) + 1;

                return [
                    'id' => $series->id,
                    'name' => $series->name,
                    'days' => $days,
                    'episodes' => $series->number_of_episodes,
                ];
            })
            ->filter()
            ->sortBy('days')
            ->take(10)
            ->values()
            ->toArray();

        return [
            'episodes_by_year' => $episodesByYear,
            'highest_rated' => $highestRated,
            'longest_completed' => $longestCompleted,
            'fastest_binge' => $fastestBinge,
        ];
    }

    private function getComparisons(): array
    {
        $allSeries = TvSeries::with('seasons.episodes.watches')->get();

        // Watched vs Unwatched Time
        $totalMinutes = $allSeries->sum(fn ($s) => $s->seasons->flatMap(fn ($season) => $season->episodes)->sum('runtime'));
        $watchedMinutes = EpisodeWatch::with('episode')->get()->sum(fn ($w) => $w->episode->runtime ?? 0);
        $unwatchedMinutes = $totalMinutes - $watchedMinutes;

        // Season Completion
        $totalSeasons = $allSeries->sum(fn ($s) => $s->seasons->count());
        $fullyWatchedSeasons = $allSeries->sum(function ($series) {
            return $series->seasons->filter(fn ($season) => $season->completion_percentage >= 100)->count();
        });
        $partiallyWatchedSeasons = $allSeries->sum(function ($series) {
            return $series->seasons->filter(fn ($season) => $season->episodes_watched > 0 && $season->completion_percentage < 100)->count();
        });

        // Year-over-Year Growth
        $watchesByYear = EpisodeWatch::whereNotNull('watched_at')
            ->get()
            ->groupBy(fn ($w) => $w->watched_at->year)
            ->map(fn ($group) => $group->count())
            ->sortKeys();

        $yoyGrowth = [];
        $previousYear = null;
        $previousCount = null;

        foreach ($watchesByYear as $year => $count) {
            if ($previousYear !== null) {
                $growth = $previousCount > 0 ? round((($count - $previousCount) / $previousCount) * 100, 1) : 0;
                $yoyGrowth[$year] = [
                    'count' => $count,
                    'growth' => $growth,
                ];
            }

            $previousYear = $year;
            $previousCount = $count;
        }

        return [
            'watched_hours' => round($watchedMinutes / 60, 1),
            'unwatched_hours' => round($unwatchedMinutes / 60, 1),
            'watched_percentage' => $totalMinutes > 0 ? round(($watchedMinutes / $totalMinutes) * 100, 1) : 0,
            'total_seasons' => $totalSeasons,
            'fully_watched_seasons' => $fullyWatchedSeasons,
            'partially_watched_seasons' => $partiallyWatchedSeasons,
            'unwatched_seasons' => $totalSeasons - $fullyWatchedSeasons - $partiallyWatchedSeasons,
            'year_over_year_growth' => $yoyGrowth,
        ];
    }
}
