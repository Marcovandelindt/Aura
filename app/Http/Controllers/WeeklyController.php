<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\EpisodeWatch;
use App\Models\Expense;
use App\Models\HealthEntry;
use App\Models\MovieWatch;
use App\Models\NintendoSwitchSession;
use App\Models\PlayedTrack;
use App\Models\PlayStationSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WeeklyController extends Controller
{
    public function index(Request $request): View
    {
        $weekOffset = (int) $request->get('week', 0);
        $weekStart = Carbon::now()->startOfWeek()->subWeeks(abs($weekOffset));
        $weekEnd = $weekStart->copy()->endOfWeek();

        $data = [
            'strava' => $this->getStravaData($weekStart, $weekEnd),
            'music' => $this->getMusicData($weekStart, $weekEnd),
            'watching' => $this->getWatchingData($weekStart, $weekEnd),
            'gaming' => $this->getGamingData($weekStart, $weekEnd),
            'health' => $this->getHealthData($weekStart, $weekEnd),
            'expenses' => $this->getExpensesData($weekStart, $weekEnd),
        ];

        return view('weekly.index', compact('data', 'weekStart', 'weekEnd', 'weekOffset'));
    }

    private function getStravaData(Carbon $start, Carbon $end): array
    {
        $activities = Activity::whereBetween('start_date_local', [$start, $end])->get();

        return [
            'count' => $activities->count(),
            'total_km' => round($activities->sum('distance') / 1000, 1),
            'total_time' => $activities->sum('moving_time'),
            'total_elevation' => round($activities->sum('total_elevation_gain')),
            'best' => $activities->sortByDesc('distance')->first(),
        ];
    }

    private function getMusicData(Carbon $start, Carbon $end): array
    {
        $tracks = PlayedTrack::whereBetween('played_at', [$start, $end])->get();

        $topTrack = $tracks->groupBy('spotify_track_id')
            ->map(fn ($g) => ['track' => $g->first(), 'count' => $g->count()])
            ->sortByDesc('count')
            ->first();

        $topArtist = $tracks->flatMap(fn ($t) => $t->artist_names)
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first();

        return [
            'count' => $tracks->count(),
            'unique_tracks' => $tracks->unique('spotify_track_id')->count(),
            'top_track' => $topTrack,
            'top_artist' => $topArtist,
        ];
    }

    private function getWatchingData(Carbon $start, Carbon $end): array
    {
        $movies = MovieWatch::with('movie')->whereBetween('watched_at', [$start, $end])->get();
        $episodes = EpisodeWatch::with('episode.season.series')->whereBetween('watched_at', [$start, $end])->get();

        return [
            'movies_count' => $movies->count(),
            'episodes_count' => $episodes->count(),
            'movies' => $movies,
            'series' => $episodes->groupBy(fn ($e) => $e->episode->season->series->name),
        ];
    }

    private function getGamingData(Carbon $start, Carbon $end): array
    {
        $ps = PlayStationSession::whereBetween('started_at', [$start, $end])->with('game')->get();
        $ns = NintendoSwitchSession::whereBetween('date', [$start->toDateString(), $end->toDateString()])->with('game')->get();

        $psMinutes = $ps->sum('duration_minutes');
        $nsMinutes = $ns->sum('duration_minutes');
        $totalMinutes = $psMinutes + $nsMinutes;

        $topPs = $ps->groupBy('play_station_game_id')
            ->map(fn ($g) => ['game' => $g->first()->game, 'minutes' => $g->sum('duration_minutes')])
            ->sortByDesc('minutes')->first();

        $topNs = $ns->groupBy('nintendo_switch_game_id')
            ->map(fn ($g) => ['game' => $g->first()->game, 'minutes' => $g->sum('duration_minutes')])
            ->sortByDesc('minutes')->first();

        return [
            'total_minutes' => $totalMinutes,
            'ps_minutes' => $psMinutes,
            'ns_minutes' => $nsMinutes,
            'top_ps' => $topPs,
            'top_ns' => $topNs,
        ];
    }

    private function getHealthData(Carbon $start, Carbon $end): array
    {
        $entries = HealthEntry::whereBetween('date', [$start->toDateString(), $end->toDateString()])->get();

        return [
            'total_steps' => $entries->sum('steps'),
            'avg_steps' => $entries->whereNotNull('steps')->avg('steps'),
            'avg_heart_rate' => $entries->whereNotNull('avg_heart_rate')->avg('avg_heart_rate'),
            'best_day' => $entries->sortByDesc('steps')->first(),
        ];
    }

    private function getExpensesData(Carbon $start, Carbon $end): array
    {
        $expenses = Expense::with('category')->whereBetween('date', [$start->toDateString(), $end->toDateString()])->get();

        return [
            'total' => $expenses->sum('amount'),
            'count' => $expenses->count(),
            'by_category' => $expenses->groupBy('category.name')
                ->map(fn ($g) => ['category' => $g->first()->category, 'total' => $g->sum('amount')])
                ->sortByDesc('total'),
            'biggest' => $expenses->sortByDesc('amount')->first(),
        ];
    }
}
