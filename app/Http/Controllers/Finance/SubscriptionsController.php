<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreSubscriptionRequest;
use App\Http\Requests\Finance\UpdateSubscriptionRequest;
use App\Models\ExpenseCategory;
use App\Models\ExpenseSubcategory;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SubscriptionsController extends Controller
{
    public function index(): View
    {
        $subscriptions = Subscription::with(['category', 'subcategory'])->ordered()->get();
        $categories = ExpenseCategory::ordered()->with('subcategories')->get();
        $subcategories = ExpenseSubcategory::orderBy('name')->get();

        return view('expenses.subscriptions', compact('subscriptions', 'categories', 'subcategories'));
    }

    public function store(StoreSubscriptionRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['is_active'] = $data['is_active'] ?? true;

            $subscription = Subscription::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Abonnement toegevoegd',
                'subscription' => $subscription->load(['category', 'subcategory']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fout bij toevoegen: '.$e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateSubscriptionRequest $request, Subscription $subscription): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['is_active'] = $data['is_active'] ?? false;

            if ($data['is_active'] && $subscription->cancelled_at) {
                $data['cancelled_at'] = null;
            }

            $subscription->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Abonnement bijgewerkt',
                'subscription' => $subscription->fresh()->load(['category', 'subcategory']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fout bij bijwerken: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Subscription $subscription): JsonResponse
    {
        try {
            if ($subscription->expenses()->exists()) {
                $subscription->update(['is_active' => false, 'cancelled_at' => now()]);

                return response()->json([
                    'success' => true,
                    'message' => 'Abonnement gedeactiveerd (heeft gekoppelde uitgaven)',
                ]);
            }

            $subscription->delete();

            return response()->json([
                'success' => true,
                'message' => 'Abonnement verwijderd',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fout bij verwijderen: '.$e->getMessage(),
            ], 500);
        }
    }
}
