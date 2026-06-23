<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreExpenseRequest;
use App\Http\Requests\Finance\UpdateExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseSubcategory;
use App\Models\Merchant;
use App\Models\Subscription;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpensesController extends Controller
{
    public function index(Request $request): View
    {
        $categoryId = $request->get('category');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Expense::with(['category', 'subcategory', 'merchant', 'subscription', 'tags'])
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        if ($categoryId) {
            $query->where('expense_category_id', $categoryId);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('date', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        $expenses = $query->paginate(25)->withQueryString();
        $categories = ExpenseCategory::ordered()->get();
        $subcategories = ExpenseSubcategory::orderBy('name')->get();
        $merchants = Merchant::ordered()->get();
        $subscriptions = Subscription::active()->ordered()->with(['category', 'subcategory'])->get();
        $tags = Tag::ordered()->get();
        $stats = $this->getOverviewStats();

        return view('expenses.index', compact(
            'expenses', 'categories', 'subcategories', 'merchants', 'subscriptions', 'tags', 'stats'
        ));
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        try {
            $data = $request->safe()->except('tags');
            $expense = Expense::create($data);

            $this->syncTags($expense, $request->input('tags', []));

            return response()->json([
                'success' => true,
                'message' => 'Uitgave toegevoegd',
                'expense' => $expense->load(['category', 'subcategory', 'merchant', 'tags']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fout bij toevoegen uitgave: '.$e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): JsonResponse
    {
        try {
            $data = $request->safe()->except('tags');
            $expense->update($data);

            $this->syncTags($expense, $request->input('tags', []));

            return response()->json([
                'success' => true,
                'message' => 'Uitgave bijgewerkt',
                'expense' => $expense->fresh()->load(['category', 'subcategory', 'merchant', 'tags']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fout bij bijwerken uitgave: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Expense $expense): JsonResponse
    {
        try {
            $expense->delete();

            return response()->json([
                'success' => true,
                'message' => 'Uitgave verwijderd',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fout bij verwijderen uitgave: '.$e->getMessage(),
            ], 500);
        }
    }

    private function syncTags(Expense $expense, array $tagNames): void
    {
        $tagNames = array_filter(array_map('trim', $tagNames));

        if (empty($tagNames)) {
            $expense->tags()->detach();

            return;
        }

        $tagIds = collect($tagNames)->map(function (string $name) {
            return Tag::firstOrCreate(['name' => $name])->id;
        });

        $expense->tags()->sync($tagIds);
    }

    private function getOverviewStats(): array
    {
        $thisWeek = Expense::thisWeek()->sum('amount');
        $thisMonth = Expense::thisMonth()->sum('amount');

        $startOfLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfLastWeek = Carbon::now()->subWeek()->endOfWeek();
        $lastWeek = Expense::whereBetween('date', [$startOfLastWeek, $endOfLastWeek])->sum('amount');

        $changePercentage = $lastWeek > 0 ? (($thisWeek - $lastWeek) / $lastWeek) * 100 : 0;

        $daysThisMonth = now()->day;
        $avgPerDay = $daysThisMonth > 0 ? $thisMonth / $daysThisMonth : 0;

        $totalExpenses = Expense::count();

        return [
            'this_week' => $thisWeek,
            'this_week_formatted' => '€'.number_format($thisWeek, 2, ',', '.'),
            'this_month' => $thisMonth,
            'this_month_formatted' => '€'.number_format($thisMonth, 2, ',', '.'),
            'avg_per_day' => $avgPerDay,
            'avg_per_day_formatted' => '€'.number_format($avgPerDay, 2, ',', '.'),
            'total_expenses' => $totalExpenses,
            'change_percentage' => round($changePercentage),
            'change_text' => ($changePercentage >= 0 ? '+' : '').round($changePercentage).'% vs vorige week',
            'change_class' => $changePercentage > 10 ? 'negative' : ($changePercentage < -10 ? 'positive' : 'neutral'),
        ];
    }
}
