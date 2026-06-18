<?php

namespace App\Http\Controllers\Agreements;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInsightRequest;
use App\Models\Insight;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InsightController extends Controller
{
    public function index(): View
    {
        $insights = Insight::latest()->get();

        return view('agreements.insights', compact('insights'));
    }

    public function store(StoreInsightRequest $request): RedirectResponse
    {
        Insight::create($request->validated());

        return redirect()->route('insights.index')->with('success', 'Inzicht opgeslagen.');
    }

    public function destroy(Insight $insight): RedirectResponse
    {
        $insight->delete();

        return redirect()->route('insights.index')->with('success', 'Inzicht verwijderd.');
    }
}
