<?php

namespace App\Http\Controllers\Agreements;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInsightRequest;
use App\Models\Insight;
use App\Services\TelegramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InsightController extends Controller
{
    public function index(): View
    {
        $insights = Insight::latest()->get();

        return view('agreements.insights', compact('insights'));
    }

    public function store(StoreInsightRequest $request, TelegramService $telegram): RedirectResponse
    {
        $insight = Insight::create($request->validated());

        $telegram->sendInsight(
            $insight->body,
            $insight->created_at->locale('nl')->translatedFormat('d F Y')
        );

        return redirect()->route('insights.index')->with('success', 'Inzicht opgeslagen.');
    }

    public function resend(Insight $insight, TelegramService $telegram): RedirectResponse
    {
        $telegram->sendInsight(
            $insight->body,
            $insight->created_at->locale('nl')->translatedFormat('d F Y')
        );

        return redirect()->route('insights.index')->with('success', 'Inzicht verstuurd naar Telegram.');
    }

    public function destroy(Insight $insight): RedirectResponse
    {
        $insight->delete();

        return redirect()->route('insights.index')->with('success', 'Inzicht verwijderd.');
    }
}
