<?php

namespace App\Http\Controllers\Strava;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Carbon\Carbon;
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
        $streaks = $this->getStreaks();
        $calendarData = $this->getCalendarData();
        $yearComparison = $this->getYearComparison();
        $monthlyData = $this->getMonthlyDistance();

        return view('strava.stats', compact(
            'records',
            'weeklyDistanceData',
            'typeDistribution',
            'weekdayData',
            'streaks',
            'calendarData',
            'yearComparison',
            'monthlyData',
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
            'labels' => $rows->map(fn ($r) => Carbon::parse($r->week_start)->format('d M'))->toArray(),
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

        $counts = [];
        foreach ([2, 3, 4, 5, 6, 7, 1] as $dayNum) {
            $counts[] = $rows[$dayNum] ?? 0;
        }

        return ['labels' => $days, 'counts' => $counts];
    }

    private function getStreaks(): array
    {
        $dates = Activity::query()
            ->selectRaw('DATE(start_date_local) as activity_date')
            ->groupBy('activity_date')
            ->orderBy('activity_date')
            ->pluck('activity_date')
            ->values()
            ->toArray();

        $total = count($dates);

        if ($total === 0) {
            return ['current' => 0, 'longest' => 0, 'total_active_days' => 0];
        }

        $isConsecutive = fn (string $a, string $b): bool =>
            Carbon::parse($a)->addDay()->format('Y-m-d') === $b;

        // Longest streak
        $longestStreak = 1;
        $streak = 1;
        for ($i = 1; $i < $total; $i++) {
            if ($isConsecutive($dates[$i - 1], $dates[$i])) {
                $streak++;
                $longestStreak = max($longestStreak, $streak);
            } else {
                $streak = 1;
            }
        }

        // Current streak (walk back from the last date)
        $currentStreak = 0;
        $today = Carbon::today()->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        if ($dates[$total - 1] === $today || $dates[$total - 1] === $yesterday) {
            $currentStreak = 1;
            for ($i = $total - 2; $i >= 0; $i--) {
                if ($isConsecutive($dates[$i], $dates[$i + 1])) {
                    $currentStreak++;
                } else {
                    break;
                }
            }
        }

        return [
            'current' => $currentStreak,
            'longest' => $longestStreak,
            'total_active_days' => $total,
        ];
    }

    /** @return array<string, int> */
    private function getCalendarData(): array
    {
        $start = Carbon::today()->subYear()->startOfDay();

        return Activity::query()
            ->where('start_date_local', '>=', $start)
            ->selectRaw('DATE(start_date_local) as day, COUNT(*) as count, SUM(distance)/1000 as km')
            ->groupBy('day')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->day => ['count' => $r->count, 'km' => round($r->km, 1)]])
            ->toArray();
    }

    private function getYearComparison(): array
    {
        $rows = Activity::query()
            ->select(
                DB::raw('YEAR(start_date_local) as year'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(distance)/1000 as total_km'),
                DB::raw('SUM(moving_time) as total_time'),
                DB::raw('SUM(total_elevation_gain) as total_elevation'),
            )
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        return $rows->map(fn ($r) => [
            'year' => $r->year,
            'count' => $r->count,
            'total_km' => round($r->total_km, 1),
            'total_time' => $r->total_time,
            'total_elevation' => round($r->total_elevation),
        ])->toArray();
    }

    private function getMonthlyDistance(): array
    {
        $rows = Activity::query()
            ->where('start_date_local', '>=', now()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw('DATE_FORMAT(start_date_local, "%Y-%m") as month'),
                DB::raw('SUM(distance)/1000 as total_km'),
                DB::raw('COUNT(*) as count'),
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'labels' => $rows->map(fn ($r) => Carbon::parse($r->month.'-01')->format('M Y'))->toArray(),
            'km' => $rows->map(fn ($r) => round($r->total_km, 1))->toArray(),
        ];
    }
}
