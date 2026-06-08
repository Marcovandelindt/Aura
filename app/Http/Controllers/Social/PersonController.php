<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use App\Models\EpisodeWatch;
use App\Models\Person;
use Illuminate\View\View;

class PersonController extends Controller
{
    public function show(Person $person): View
    {
        $person->load([
            'movies' => fn ($q) => $q->orderBy('last_watched_at', 'desc'),
            'tvSeries' => fn ($q) => $q->orderBy('episodes_watched', 'desc'),
        ]);

        $watchedMovies = $person->movies->where('watch_count', '>', 0);
        $totalMovieWatches = $watchedMovies->sum('watch_count');
        $totalMovieMinutes = $watchedMovies->sum(fn ($m) => ($m->runtime ?? 0) * $m->watch_count);

        $tvSeriesIds = $person->tvSeries->pluck('id');
        $totalEpisodesWatched = $person->tvSeries->sum('episodes_watched');
        $totalTvMinutes = $this->calculateTvMinutes($tvSeriesIds->toArray());

        $totalMinutes = $totalMovieMinutes + $totalTvMinutes;

        $allFirstSeen = collect()
            ->merge($watchedMovies->pluck('first_watched_at'))
            ->merge($person->tvSeries->where('episodes_watched', '>', 0)->pluck('first_watched_at'))
            ->filter()
            ->sort();

        $allLastSeen = collect()
            ->merge($watchedMovies->pluck('last_watched_at'))
            ->merge($person->tvSeries->where('episodes_watched', '>', 0)->pluck('last_watched_at'))
            ->filter()
            ->sort();

        return view('people.show', [
            'person' => $person,
            'watchedMovies' => $watchedMovies,
            'totalMovieWatches' => $totalMovieWatches,
            'totalEpisodesWatched' => $totalEpisodesWatched,
            'totalHours' => round($totalMinutes / 60, 1),
            'firstSeen' => $allFirstSeen->first(),
            'lastSeen' => $allLastSeen->last(),
        ]);
    }

    /** @param array<int> $tvSeriesIds */
    private function calculateTvMinutes(array $tvSeriesIds): int
    {
        if (empty($tvSeriesIds)) {
            return 0;
        }

        return (int) EpisodeWatch::query()
            ->join('tv_episodes', 'tv_episodes.id', '=', 'episode_watches.tv_episode_id')
            ->join('tv_seasons', 'tv_seasons.id', '=', 'tv_episodes.tv_season_id')
            ->whereIn('tv_seasons.tv_series_id', $tvSeriesIds)
            ->whereNotNull('tv_episodes.runtime')
            ->sum('tv_episodes.runtime');
    }
}
