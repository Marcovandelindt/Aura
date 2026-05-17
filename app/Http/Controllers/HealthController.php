<?php

namespace App\Http\Controllers;

use App\Models\HealthEntry;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HealthController extends Controller
{
    public function index(Request $request): View
    {
        $month = $request->get('month', now()->format('Y-m'));
        $currentMonth = Carbon::parse($month.'-01');

        $monthlyEntries = HealthEntry::whereMonth('date', $currentMonth->month)
            ->whereYear('date', $currentMonth->year)
            ->get()
            ->keyBy(fn ($entry) => $entry->date->format('Y-m-d'));

        $entries = HealthEntry::orderByDesc('date')->paginate(25)->withQueryString();

        $stats = $this->getOverviewStats();
        $stepGoal = HealthEntry::getStepGoal();

        return view('health.index', compact(
            'entries',
            'monthlyEntries',
            'stats',
            'currentMonth',
            'stepGoal'
        ));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date', 'unique:health_entries,date'],
            'steps' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'avg_heart_rate' => ['nullable', 'integer', 'min:30', 'max:250'],
            'resting_heart_rate' => ['nullable', 'integer', 'min:30', 'max:150'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $entry = HealthEntry::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Health entry added for '.$entry->date->format('d M Y'),
                'entry' => $entry,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add health entry: '.$e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, HealthEntry $entry): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date', 'unique:health_entries,date,'.$entry->id],
            'steps' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'avg_heart_rate' => ['nullable', 'integer', 'min:30', 'max:250'],
            'resting_heart_rate' => ['nullable', 'integer', 'min:30', 'max:150'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $entry->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Health entry updated successfully',
                'entry' => $entry->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update health entry: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy(HealthEntry $entry): JsonResponse
    {
        try {
            $date = $entry->date->format('d M Y');
            $entry->delete();

            return response()->json([
                'success' => true,
                'message' => 'Health entry for '.$date.' deleted',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete health entry: '.$e->getMessage(),
            ], 500);
        }
    }

    public function export(Request $request): StreamedResponse
    {
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();
        $stepGoal = HealthEntry::getStepGoal();

        $entries = HealthEntry::whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        $filename = 'health_steps_'.$startDate->format('Y-m-d').'_to_'.$endDate->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($entries, $stepGoal) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['date', 'day_of_week', 'steps', 'step_goal', 'goal_met', 'avg_heart_rate', 'resting_heart_rate', 'notes']);

            foreach ($entries as $entry) {
                fputcsv($handle, [
                    $entry->date->format('Y-m-d'),
                    $entry->date->format('l'),
                    $entry->steps ?? '',
                    $stepGoal,
                    $entry->steps !== null ? ($entry->meetsStepGoal() ? 'yes' : 'no') : '',
                    $entry->avg_heart_rate ?? '',
                    $entry->resting_heart_rate ?? '',
                    $entry->notes ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function getOverviewStats(): array
    {
        $allEntries = HealthEntry::count();
        $thisWeek = HealthEntry::thisWeek()->count();
        $thisMonth = HealthEntry::thisMonth()->count();

        $avgSteps = HealthEntry::whereNotNull('steps')->avg('steps');
        $avgHeartRate = HealthEntry::whereNotNull('avg_heart_rate')->avg('avg_heart_rate');
        $avgRestingHr = HealthEntry::whereNotNull('resting_heart_rate')->avg('resting_heart_rate');

        $stepGoal = HealthEntry::getStepGoal();
        $stepGoalMet = HealthEntry::where('steps', '>=', $stepGoal)->count();
        $entriesWithSteps = HealthEntry::whereNotNull('steps')->count();

        return [
            'total_entries' => $allEntries,
            'entries_this_week' => $thisWeek,
            'entries_this_month' => $thisMonth,
            'avg_steps' => $avgSteps ? round($avgSteps) : null,
            'avg_heart_rate' => $avgHeartRate ? round($avgHeartRate) : null,
            'avg_resting_hr' => $avgRestingHr ? round($avgRestingHr) : null,
            'goal_achievement_rate' => $entriesWithSteps > 0
                ? round(($stepGoalMet / $entriesWithSteps) * 100, 1)
                : 0,
        ];
    }
}
