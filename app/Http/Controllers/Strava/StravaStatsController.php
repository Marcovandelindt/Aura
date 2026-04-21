<?php

namespace App\Http\Controllers\Strava;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StravaStatsController extends Controller
{
    public function index(): View
    {
        $records = $this->getPersonalRecords();
        $weeklyDistanceData = $this->getWeeklyDistance();
        $typeDistribution = $this->getTypeDistribution();
        $weekdayData = $this->getWeekdayDistribution();

        return view('strava.stats', compact(
            'records',
            'weeklyDistanceData',
            'typeDistribution',
            'weekdayData',
        ));
    }

    private function getPersonalRecords(): array
    {
        return [
            'longest_distance' => Activity::orderByDesc('distance')->first(),
            'longest_duration' => Activity::orderByDesc('moving_time')->first(),
            'most_elevation' => Activity::orderByDesc('total_elevation_gain')->first(),
            'fastest_pace' => Activity::whereIn('sport_type', ['Run', 'Walk'])
                ->where('distance', '>', 1000)
                ->orderByDesc('average_speed')
                ->first(),
        ];
    }

    private function getWeeklyDistance(): array
    {
        $rows = Activity::query()
            ->where('start_date', '>=', now()->subWeeks(12))
            ->select(
                DB::raw('YEARWEEK(start_date, 1) as week'),
                DB::raw('MIN(DATE(start_date)) as week_start'),
                DB::raw('SUM(distance) / 1000 as total_km'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('week')
            ->orderBy('week')
            ->get();

        return [
            'labels' => $rows->map(fn ($r) => \Carbon\Carbon::parse($r->week_start)->format('d M'))->toArray(),
            'km' => $rows->map(fn ($r) => round($r->total_km, 1))->toArray(),
            'count' => $rows->map(fn ($r) => $r->count)->toArray(),
        ];
    }

    private function getTypeDistribution(): array
    {
        $rows = Activity::query()
            ->select('sport_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(distance)/1000 as total_km'))
            ->groupBy('sport_type')
            ->orderByDesc('count')
            ->get();

        return [
            'labels' => $rows->pluck('sport_type')->toArray(),
            'counts' => $rows->map(fn ($r) => $r->count)->toArray(),
            'km' => $rows->map(fn ($r) => round($r->total_km, 1))->toArray(),
        ];
    }

    private function getWeekdayDistribution(): array
    {
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        $rows = Activity::query()
            ->select(DB::raw('DAYOFWEEK(start_date) as day_num'), DB::raw('COUNT(*) as count'))
            ->groupBy('day_num')
            ->orderBy('day_num')
            ->pluck('count', 'day_num');

        // MySQL DAYOFWEEK: 1=Sun, 2=Mon, ..., 7=Sat → remap to Mon-Sun
        $counts = [];
        foreach ([2, 3, 4, 5, 6, 7, 1] as $dayNum) {
            $counts[] = $rows[$dayNum] ?? 0;
        }

        return ['labels' => $days, 'counts' => $counts];
    }
}
