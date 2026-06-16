<?php

namespace App\Http\Controllers\Agreements;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAgreementCheckInRequest;
use App\Http\Requests\StoreAgreementRequest;
use App\Http\Requests\StoreGoalRequest;
use App\Models\Agreement;
use App\Models\AgreementCheckIn;
use App\Models\Goal;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AgreementController extends Controller
{
    public function index(): View
    {
        $agreements = Agreement::active()->get();
        $goals = Goal::active()->orderBy('target_date')->get();
        $today = now()->startOfDay();

        $todayCheckIns = AgreementCheckIn::whereDate('checked_on', $today)
            ->pluck('status', 'agreement_id');

        return view('agreements.index', compact('agreements', 'goals', 'todayCheckIns', 'today'));
    }

    public function storeAgreement(StoreAgreementRequest $request): RedirectResponse
    {
        Agreement::create($request->validated());

        return redirect()->route('agreements.index')->with('success', 'Afspraak toegevoegd.');
    }

    public function destroyAgreement(Agreement $agreement): RedirectResponse
    {
        $agreement->update(['is_active' => false]);

        return redirect()->route('agreements.index')->with('success', 'Afspraak gedeactiveerd.');
    }

    public function storeCheckIn(StoreAgreementCheckInRequest $request, Agreement $agreement): RedirectResponse
    {
        AgreementCheckIn::updateOrCreate(
            [
                'agreement_id' => $agreement->id,
                'checked_on' => $request->validated('checked_on'),
            ],
            [
                'status' => $request->validated('status'),
                'notes' => $request->validated('notes'),
            ]
        );

        return redirect()->route('agreements.index')->with('success', 'Check-in opgeslagen.');
    }

    public function storeGoal(StoreGoalRequest $request): RedirectResponse
    {
        Goal::create($request->validated());

        return redirect()->route('agreements.index')->with('success', 'Doel toegevoegd.');
    }

    public function updateGoal(Goal $goal, StoreGoalRequest $request): RedirectResponse
    {
        $goal->update($request->validated());

        return redirect()->route('agreements.index')->with('success', 'Doel bijgewerkt.');
    }
}
