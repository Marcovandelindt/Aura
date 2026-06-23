<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreMerchantRequest;
use App\Models\Merchant;
use Illuminate\Http\JsonResponse;

class MerchantsController extends Controller
{
    public function store(StoreMerchantRequest $request): JsonResponse
    {
        try {
            $merchant = Merchant::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Merchant toegevoegd',
                'merchant' => $merchant,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fout bij toevoegen merchant: '.$e->getMessage(),
            ], 500);
        }
    }
}
