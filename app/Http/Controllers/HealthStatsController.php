<?php

namespace App\Http\Controllers;

use App\Models\HealthEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HealthStatsController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->get('filter', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $dateRange = $this->getDateRange($filter, $startDate, $endDate);

        $stats = [
            'overview' => $this->getOverviewStats($dateRange['start'], $dateRange['end']),
            'trends' => $this->getTrends($dateRange['start'], $dateRange['end']),
            'weekly_comparison' => $this->getWeeklyComparison(),
            'monthly_comparison' => $this->getMonthlyComparison(),
            'step_goals' => $this->getStepGoalStats($dateRange['start'], $dateRange['end']),
            'streaks' => $this->getStreakStats(),
            'best_days' => $this->getBestDays($dateRange['start'], $dateRange['end']),
            'heart_rate_stats' => $this->getHeartRateStats($dateRange['start'], $dateRange['end']),
            'weekday_patterns' => $this->getWeekdayPatterns($dateRange['start'], $dateRange['end']),
            'monthly_stats' => $this->getMonthlyStats(),
        ];

        $filterData = [
            'current' => $filter,
            'start_date' => $dateRange['start']?->format('Y-m-d'),
            'end_date' => $dateRange['end']?->format('Y-m-d'),
        ];

        return view('health.stats', compact('stats', 'filterData'));
    }

    private function getDateRange(string $filter, ?string $startDate, ?string $endDate): array
    {
        if ($filter === 'custom' && $startDate && $endDate) {
            return [
                'start' => Carbon::parse($startDate)->startOfDay(),
                'end' => Carbon::parse($endDate)->endOfDay(),
            ];
        }

        $end = now()->endOfDay();

        return match ($filter) {
            '7' => ['start' => now()->subDays(6)->startOfDay(), 'end' => $end],
            '30' => ['start' => now()->subDays(29)->startOfDay(), 'end' => $end],
            '90' => ['start' => now()->subDays(89)->startOfDay(), 'end' => $end],
            '365' => ['start' => now()->subDays(364)->startOfDay(), 'end' => $end],
            'all' => ['start' => null, 'end' => null],
            default => ['start' => now()->subDays(29)->startOfDay(), 'end' => $end],
        };
    }

    private function getOverviewStats(?Carbon $startDate, ?Carbon $endDate): array
    {
        $query = HealthEntry::query();
        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        $totalEntries = (clone $query)->count();
        $totalSteps = (clone $query)->whereNotNull('steps')->sum('steps');
        $avgSteps = (clone $query)->whereNotNull('steps')->avg('steps');
        $totalKm = $totalSteps * 0.00075; // ~0.75m per step average
        $avgHeartRate = (clone $query)->whereNotNull('avg_heart_rate')->avg('avg_heart_rate');
        $avgRestingHr = (clone $query)->whereNotNull('resting_heart_rate')->avg('resting_heart_rate');

        $firstEntry = (clone $query)->orderBy('date')->first();
        $lastEntry = (clone $query)->orderByDesc('date')->first();

        return [
            'total_entries' => $totalEntries,
            'total_steps' => $totalSteps,
            'total_km' => round($totalKm, 1),
            'avg_steps' => $avgSteps ? round($avgSteps) : null,
            'avg_heart_rate' => $avgHeartRate ? round($avgHeartRate) : null,
            'avg_resting_hr' => $avgRestingHr ? round($avgRestingHr) : null,
            'first_entry_date' => $firstEntry?->date,
            'last_entry_date' => $lastEntry?->date,
        ];
    }

    private function getTrends(?Carbon $startDate, ?Carbon $endDate): array
    {
        $query = HealthEntry::query()->orderBy('date');

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        return $query->get()
            ->map(fn ($entry) => [
                'date' => $entry->date->format('Y-m-d'),
                'date_formatted' => $entry->date->format('d M'),
                'steps' => $entry->steps,
                'avg_heart_rate' => $entry->avg_heart_rate,
                'resting_heart_rate' => $entry->resting_heart_rate,
                'met_goal' => $entry->meetsStepGoal(),
            ])
            ->toArray();
    }

    private function getWeeklyComparison(): array
    {
        $thisWeekStart = now()->startOfWeek();
        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();

        $thisWeek = HealthEntry::where('date', '>=', $thisWeekStart)->get();
        $lastWeek = HealthEntry::whereBetween('date', [$lastWeekStart, $lastWeekEnd])->get();

        $thisWeekSteps = $thisWeek->sum('steps');
        $lastWeekSteps = $lastWeek->sum('steps');
        $stepsDiff = $thisWeekSteps - $lastWeekSteps;
        $stepsPercentChange = $lastWeekSteps > 0 ? round(($stepsDiff / $lastWeekSteps) * 100, 1) : 0;

        $thisWeekAvgSteps = $thisWeek->whereNotNull('steps')->avg('steps');
        $lastWeekAvgSteps = $lastWeek->whereNotNull('steps')->avg('steps');

        return [
            'this_week' => [
                'entries' => $thisWeek->count(),
                'total_steps' => $thisWeekSteps,
                'avg_steps' => $thisWeekAvgSteps ? round($thisWeekAvgSteps) : null,
                'avg_heart_rate' => $thisWeek->whereNotNull('avg_heart_rate')->avg('avg_heart_rate') ? round($thisWeek->whereNotNull('avg_heart_rate')->avg('avg_heart_rate')) : null,
            ],
            'last_week' => [
                'entries' => $lastWeek->count(),
                'total_steps' => $lastWeekSteps,
                'avg_steps' => $lastWeekAvgSteps ? round($lastWeekAvgSteps) : null,
                'avg_heart_rate' => $lastWeek->whereNotNull('avg_heart_rate')->avg('avg_heart_rate') ? round($lastWeek->whereNotNull('avg_heart_rate')->avg('avg_heart_rate')) : null,
            ],
            'steps_diff' => $stepsDiff,
            'steps_percent_change' => $stepsPercentChange,
            'trend' => $stepsDiff > 0 ? 'up' : ($stepsDiff < 0 ? 'down' : 'same'),
        ];
    }

    private function getMonthlyComparison(): array
    {
        $thisMonth = HealthEntry::thisMonth()->get();
        $lastMonth = HealthEntry::whereMonth('date', now()->subMonth()->month)
            ->whereYear('date', now()->subMonth()->year)
            ->get();

        $thisMonthSteps = $thisMonth->sum('steps');
        $lastMonthSteps = $lastMonth->sum('steps');
        $stepsDiff = $thisMonthSteps - $lastMonthSteps;
        $stepsPercentChange = $lastMonthSteps > 0 ? round(($stepsDiff / $lastMonthSteps) * 100, 1) : 0;

        return [
            'this_month' => [
                'name' => now()->format('F'),
                'entries' => $thisMonth->count(),
                'total_steps' => $thisMonthSteps,
                'avg_steps' => $thisMonth->whereNotNull('steps')->avg('steps') ? round($thisMonth->whereNotNull('steps')->avg('steps')) : null,
            ],
            'last_month' => [
                'name' => now()->subMonth()->format('F'),
                'entries' => $lastMonth->count(),
                'total_steps' => $lastMonthSteps,
                'avg_steps' => $lastMonth->whereNotNull('steps')->avg('steps') ? round($lastMonth->whereNotNull('steps')->avg('steps')) : null,
            ],
            'steps_diff' => $stepsDiff,
            'steps_percent_change' => $stepsPercentChange,
            'trend' => $stepsDiff > 0 ? 'up' : ($stepsDiff < 0 ? 'down' : 'same'),
        ];
    }

    private function getStepGoalStats(?Carbon $startDate, ?Carbon $endDate): array
    {
        $goal = HealthEntry::getStepGoal();

        $query = HealthEntry::query();
        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        $entriesWithSteps = (clone $query)->whereNotNull('steps')->count();
        $goalsMet = (clone $query)->where('steps', '>=', $goal)->count();
        $achievementRate = $entriesWithSteps > 0 ? round(($goalsMet / $entriesWithSteps) * 100, 1) : 0;

        return [
            'goal' => $goal,
            'total_goals_met' => $goalsMet,
            'total_entries_with_steps' => $entriesWithSteps,
            'achievement_rate' => $achievementRate,
        ];
    }

    private function getStreakStats(): array
    {
        $dates = HealthEntry::orderByDesc('date')
            ->pluck('date')
            ->map(fn ($date) => $date->format('Y-m-d'))
            ->toArray();

        if (empty($dates)) {
            return [
                'current_streak' => 0,
                'longest_streak' => 0,
                'goal_streak' => 0,
            ];
        }

        // Current streak
        $currentStreak = $this->calculateCurrentStreak($dates);
        $longestStreak = $this->calculateLongestStreak($dates);

        // Goal streak (consecutive days meeting step goal)
        $goalDates = HealthEntry::where('steps', '>=', HealthEntry::getStepGoal())
            ->orderByDesc('date')
            ->pluck('date')
            ->map(fn ($date) => $date->format('Y-m-d'))
            ->toArray();
        $goalStreak = $this->calculateCurrentStreak($goalDates);

        return [
            'current_streak' => $currentStreak,
            'longest_streak' => $longestStreak,
            'goal_streak' => $goalStreak,
        ];
    }

    private function calculateCurrentStreak(array $dates): int
    {
        if (empty($dates)) {
            return 0;
        }

        $today = Carbon::today()->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        if ($dates[0] !== $today && $dates[0] !== $yesterday) {
            return 0;
        }

        $streak = 1;
        for ($i = 1; $i < count($dates); $i++) {
            $prevDate = Carbon::parse($dates[$i - 1]);
            $currDate = Carbon::parse($dates[$i]);

            if ($prevDate->subDay()->format('Y-m-d') === $currDate->format('Y-m-d')) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }

    private function calculateLongestStreak(array $dates): int
    {
        if (count($dates) < 2) {
            return count($dates);
        }

        $sortedDates = collect($dates)->sort()->values()->toArray();
        $maxStreak = 1;
        $currentStreak = 1;

        for ($i = 1; $i < count($sortedDates); $i++) {
            $prevDate = Carbon::parse($sortedDates[$i - 1]);
            $currDate = Carbon::parse($sortedDates[$i]);

            if ($prevDate->addDay()->format('Y-m-d') === $currDate->format('Y-m-d')) {
                $currentStreak++;
                $maxStreak = max($maxStreak, $currentStreak);
            } else {
                $currentStreak = 1;
            }
        }

        return $maxStreak;
    }

    private function getBestDays(?Carbon $startDate, ?Carbon $endDate): array
    {
        $query = HealthEntry::query();
        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        $mostSteps = (clone $query)->whereNotNull('steps')
            ->orderByDesc('steps')
            ->first();

        $lowestRestingHr = (clone $query)->whereNotNull('resting_heart_rate')
            ->orderBy('resting_heart_rate')
            ->first();

        return [
            'most_steps' => $mostSteps ? [
                'date' => $mostSteps->date,
                'steps' => $mostSteps->steps,
            ] : null,
            'lowest_resting_hr' => $lowestRestingHr ? [
                'date' => $lowestRestingHr->date,
                'resting_heart_rate' => $lowestRestingHr->resting_heart_rate,
            ] : null,
        ];
    }

    private function getHeartRateStats(?Carbon $startDate, ?Carbon $endDate): array
    {
        $query = HealthEntry::query();
        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        $avgHeartRates = (clone $query)->whereNotNull('avg_heart_rate')
            ->orderBy('date')
            ->get();

        $restingHeartRates = (clone $query)->whereNotNull('resting_heart_rate')
            ->orderBy('date')
            ->get();

        $minAvgHr = $avgHeartRates->min('avg_heart_rate');
        $maxAvgHr = $avgHeartRates->max('avg_heart_rate');
        $avgAvgHr = $avgHeartRates->avg('avg_heart_rate');

        $minRestingHr = $restingHeartRates->min('resting_heart_rate');
        $maxRestingHr = $restingHeartRates->max('resting_heart_rate');
        $avgRestingHr = $restingHeartRates->avg('resting_heart_rate');

        return [
            'avg_heart_rate' => [
                'min' => $minAvgHr,
                'max' => $maxAvgHr,
                'avg' => $avgAvgHr ? round($avgAvgHr) : null,
                'count' => $avgHeartRates->count(),
            ],
            'resting_heart_rate' => [
                'min' => $minRestingHr,
                'max' => $maxRestingHr,
                'avg' => $avgRestingHr ? round($avgRestingHr) : null,
                'count' => $restingHeartRates->count(),
            ],
        ];
    }

    private function getWeekdayPatterns(?Carbon $startDate, ?Carbon $endDate): array
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        $query = HealthEntry::selectRaw('WEEKDAY(date) as day_num, AVG(steps) as avg_steps, AVG(avg_heart_rate) as avg_hr, AVG(resting_heart_rate) as avg_resting_hr, COUNT(*) as entries');

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        $stats = $query->groupBy('day_num')
            ->orderBy('day_num')
            ->get()
            ->keyBy('day_num');

        $result = [];
        for ($i = 0; $i < 7; $i++) {
            $data = $stats[$i] ?? null;
            $result[] = [
                'day' => $days[$i],
                'day_short' => substr($days[$i], 0, 3),
                'avg_steps' => $data ? round($data->avg_steps) : null,
                'avg_heart_rate' => $data ? round($data->avg_hr) : null,
                'avg_resting_hr' => $data ? round($data->avg_resting_hr) : null,
                'entries' => $data->entries ?? 0,
            ];
        }

        return $result;
    }

    private function getMonthlyStats(): array
    {
        return HealthEntry::selectRaw('YEAR(date) as year, MONTH(date) as month, COUNT(*) as entries, SUM(steps) as total_steps, AVG(steps) as avg_steps, AVG(avg_heart_rate) as avg_hr, AVG(resting_heart_rate) as avg_resting_hr')
            ->groupBy('year', 'month')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->limit(12)
            ->get()
            ->map(fn ($row) => [
                'year' => $row->year,
                'month' => $row->month,
                'month_name' => Carbon::createFromDate($row->year, $row->month, 1)->format('M Y'),
                'entries' => $row->entries,
                'total_steps' => $row->total_steps,
                'avg_steps' => $row->avg_steps ? round($row->avg_steps) : null,
                'avg_heart_rate' => $row->avg_hr ? round($row->avg_hr) : null,
                'avg_resting_hr' => $row->avg_resting_hr ? round($row->avg_resting_hr) : null,
            ])
            ->toArray();
    }
}
