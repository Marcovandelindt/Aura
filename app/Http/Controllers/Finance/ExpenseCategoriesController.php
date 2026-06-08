<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreExpenseCategoryRequest;
use App\Http\Requests\Finance\UpdateExpenseCategoryRequest;
use App\Models\ExpenseCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ExpenseCategoriesController extends Controller
{
    public function index(): View
    {
        $categories = ExpenseCategory::ordered()
            ->withCount('expenses')
            ->withSum('expenses', 'amount')
            ->get();

        return view('expenses.categories', compact('categories'));
    }

    public function store(StoreExpenseCategoryRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            if (empty($data['color'])) {
                $data['color'] = '#6366f1';
            }

            if (empty($data['icon'])) {
                $data['icon'] = 'fa-tag';
            }

            $maxOrder = ExpenseCategory::max('sort_order') ?? 0;
            $data['sort_order'] = $maxOrder + 1;

            $category = ExpenseCategory::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Categorie toegevoegd',
                'category' => $category,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fout bij toevoegen categorie: '.$e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateExpenseCategoryRequest $request, ExpenseCategory $category): JsonResponse
    {
        try {
            $category->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Categorie bijgewerkt',
                'category' => $category->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fout bij bijwerken categorie: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy(ExpenseCategory $category): JsonResponse
    {
        try {
            if ($category->expenses()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categorie kan niet worden verwijderd omdat er nog uitgaven aan gekoppeld zijn',
                ], 422);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Categorie verwijderd',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fout bij verwijderen categorie: '.$e->getMessage(),
            ], 500);
        }
    }
}
