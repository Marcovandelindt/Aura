<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseStatsController extends Controller
{
    public function index(Request $request): View
    {
        $year = $request->get('year', now()->year);

        $stats = [
            'overview' => $this->getOverviewStats($year),
            'monthly_by_category' => $this->getMonthlyByCategory($year),
            'category_totals' => $this->getCategoryTotals($year),
            'monthly_totals' => $this->getMonthlyTotals($year),
            'weekly_comparison' => $this->getWeeklyComparison(),
            'monthly_comparison' => $this->getMonthlyComparison(),
            'top_expenses' => $this->getTopExpenses($year),
            'trends' => $this->getTrends($year),
            'yearly_history' => $this->getYearlyHistory(),
        ];

        $availableYears = $this->getAvailableYears();
        $categories = ExpenseCategory::ordered()->get();

        return view('expenses.stats', compact('stats', 'year', 'availableYears', 'categories'));
    }

    private function getOverviewStats(int $year): array
    {
        $yearTotal = Expense::whereYear('date', $year)->sum('amount');
        $yearCount = Expense::whereYear('date', $year)->count();

        $thisMonth = Expense::thisMonth()->sum('amount');
        $thisWeek = Expense::thisWeek()->sum('amount');

        $avgPerMonth = $yearTotal / max(1, min(now()->month, 12));
        $avgPerExpense = $yearCount > 0 ? $yearTotal / $yearCount : 0;

        $daysInYear = now()->year == $year ? now()->dayOfYear : 365;
        $avgPerDay = $yearTotal / max(1, $daysInYear);

        return [
            'year_total' => $yearTotal,
            'year_total_formatted' => '€'.number_format($yearTotal, 2, ',', '.'),
            'year_count' => $yearCount,
            'this_month' => $thisMonth,
            'this_month_formatted' => '€'.number_format($thisMonth, 2, ',', '.'),
            'this_week' => $thisWeek,
            'this_week_formatted' => '€'.number_format($thisWeek, 2, ',', '.'),
            'avg_per_month' => $avgPerMonth,
            'avg_per_month_formatted' => '€'.number_format($avgPerMonth, 2, ',', '.'),
            'avg_per_expense' => $avgPerExpense,
            'avg_per_expense_formatted' => '€'.number_format($avgPerExpense, 2, ',', '.'),
            'avg_per_day' => $avgPerDay,
            'avg_per_day_formatted' => '€'.number_format($avgPerDay, 2, ',', '.'),
        ];
    }

    private function getMonthlyByCategory(int $year): array
    {
        $categories = ExpenseCategory::ordered()->get();
        $months = [];

        for ($m = 1; $m <= 12; $m++) {
            $monthData = [
                'month' => $m,
                'month_name' => Carbon::createFromDate($year, $m, 1)->format('M'),
                'month_name_full' => Carbon::createFromDate($year, $m, 1)->translatedFormat('F'),
                'total' => 0,
                'categories' => [],
            ];

            foreach ($categories as $category) {
                $amount = Expense::where('expense_category_id', $category->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $m)
                    ->sum('amount');

                $monthData['categories'][$category->id] = [
                    'name' => $category->name,
                    'color' => $category->color,
                    'icon' => $category->icon,
                    'amount' => $amount,
                    'formatted' => '€'.number_format($amount, 2, ',', '.'),
                ];

                $monthData['total'] += $amount;
            }

            $monthData['total_formatted'] = '€'.number_format($monthData['total'], 2, ',', '.');
            $months[] = $monthData;
        }

        return $months;
    }

    private function getCategoryTotals(int $year): array
    {
        $categories = ExpenseCategory::ordered()->get();
        $totals = [];
        $grandTotal = Expense::whereYear('date', $year)->sum('amount');

        foreach ($categories as $category) {
            $amount = Expense::where('expense_category_id', $category->id)
                ->whereYear('date', $year)
                ->sum('amount');

            $count = Expense::where('expense_category_id', $category->id)
                ->whereYear('date', $year)
                ->count();

            $percentage = $grandTotal > 0 ? ($amount / $grandTotal) * 100 : 0;

            $totals[] = [
                'id' => $category->id,
                'name' => $category->name,
                'color' => $category->color,
                'icon' => $category->icon,
                'amount' => $amount,
                'formatted' => '€'.number_format($amount, 2, ',', '.'),
                'count' => $count,
                'percentage' => round($percentage, 1),
            ];
        }

        usort($totals, fn ($a, $b) => $b['amount'] <=> $a['amount']);

        return $totals;
    }

    private function getMonthlyTotals(int $year): array
    {
        $months = [];

        for ($m = 1; $m <= 12; $m++) {
            $amount = Expense::whereYear('date', $year)
                ->whereMonth('date', $m)
                ->sum('amount');

            $count = Expense::whereYear('date', $year)
                ->whereMonth('date', $m)
                ->count();

            $months[] = [
                'month' => $m,
                'month_name' => Carbon::createFromDate($year, $m, 1)->format('M'),
                'month_name_full' => Carbon::createFromDate($year, $m, 1)->translatedFormat('F'),
                'amount' => $amount,
                'formatted' => '€'.number_format($amount, 2, ',', '.'),
                'count' => $count,
            ];
        }

        return $months;
    }

    private function getWeeklyComparison(): array
    {
        $thisWeek = Expense::thisWeek()->sum('amount');
        $thisWeekCount = Expense::thisWeek()->count();

        $startOfLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfLastWeek = Carbon::now()->subWeek()->endOfWeek();
        $lastWeek = Expense::whereBetween('date', [$startOfLastWeek, $endOfLastWeek])->sum('amount');
        $lastWeekCount = Expense::whereBetween('date', [$startOfLastWeek, $endOfLastWeek])->count();

        $diff = $thisWeek - $lastWeek;
        $percentChange = $lastWeek > 0 ? round(($diff / $lastWeek) * 100, 1) : 0;

        return [
            'this_week' => $thisWeek,
            'this_week_formatted' => '€'.number_format($thisWeek, 2, ',', '.'),
            'this_week_count' => $thisWeekCount,
            'last_week' => $lastWeek,
            'last_week_formatted' => '€'.number_format($lastWeek, 2, ',', '.'),
            'last_week_count' => $lastWeekCount,
            'diff' => $diff,
            'percent_change' => $percentChange,
            'trend' => $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'same'),
        ];
    }

    private function getMonthlyComparison(): array
    {
        $thisMonth = Expense::thisMonth()->sum('amount');
        $thisMonthCount = Expense::thisMonth()->count();

        $lastMonth = Expense::whereMonth('date', now()->subMonth()->month)
            ->whereYear('date', now()->subMonth()->year)
            ->sum('amount');
        $lastMonthCount = Expense::whereMonth('date', now()->subMonth()->month)
            ->whereYear('date', now()->subMonth()->year)
            ->count();

        $diff = $thisMonth - $lastMonth;
        $percentChange = $lastMonth > 0 ? round(($diff / $lastMonth) * 100, 1) : 0;

        return [
            'this_month' => $thisMonth,
            'this_month_formatted' => '€'.number_format($thisMonth, 2, ',', '.'),
            'this_month_count' => $thisMonthCount,
            'this_month_name' => now()->translatedFormat('F'),
            'last_month' => $lastMonth,
            'last_month_formatted' => '€'.number_format($lastMonth, 2, ',', '.'),
            'last_month_count' => $lastMonthCount,
            'last_month_name' => now()->subMonth()->translatedFormat('F'),
            'diff' => $diff,
            'percent_change' => $percentChange,
            'trend' => $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'same'),
        ];
    }

    private function getTopExpenses(int $year): array
    {
        return Expense::with('category')
            ->whereYear('date', $year)
            ->orderByDesc('amount')
            ->limit(10)
            ->get()
            ->map(fn ($expense) => [
                'id' => $expense->id,
                'amount' => $expense->amount,
                'formatted' => $expense->formatted_amount,
                'description' => $expense->description,
                'date' => $expense->date,
                'date_formatted' => $expense->date->format('d M Y'),
                'category' => [
                    'name' => $expense->category->name,
                    'color' => $expense->category->color,
                    'icon' => $expense->category->icon,
                ],
            ])
            ->toArray();
    }

    private function getTrends(int $year): array
    {
        return Expense::selectRaw('DATE(date) as expense_date, SUM(amount) as total')
            ->whereYear('date', $year)
            ->groupBy('expense_date')
            ->orderBy('expense_date')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->expense_date,
                'date_formatted' => Carbon::parse($row->expense_date)->format('d M'),
                'total' => $row->total,
            ])
            ->toArray();
    }

    private function getYearlyHistory(): array
    {
        return Expense::selectRaw('YEAR(date) as year, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('year')
            ->orderByDesc('year')
            ->get()
            ->map(fn ($row) => [
                'year' => $row->year,
                'total' => $row->total,
                'formatted' => '€'.number_format($row->total, 2, ',', '.'),
                'count' => $row->count,
                'avg_per_month' => '€'.number_format($row->total / 12, 2, ',', '.'),
            ])
            ->toArray();
    }

    private function getAvailableYears(): array
    {
        $years = Expense::selectRaw('DISTINCT YEAR(date) as year')
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        if (empty($years)) {
            $years = [now()->year];
        }

        if (! in_array(now()->year, $years)) {
            array_unshift($years, now()->year);
        }

        return $years;
    }
}
