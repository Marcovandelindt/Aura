<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Models\PlayerStats;
use App\Models\Task;
use App\Services\PlayerStatsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaakController extends Controller
{
    public function index(): View
    {
        $stats = PlayerStats::instance();

        $pendingTasks = Task::pending()
            ->where(function ($q) {
                $q->whereNull('due_date')->orWhere('due_date', '=', today());
            })
            ->orderByRaw("FIELD(difficulty, 'hard', 'normal', 'easy')")
            ->get();

        $overdueTasks = Task::pending()
            ->where('due_date', '<', today())
            ->orderBy('due_date')
            ->get();

        $upcomingTasks = Task::pending()
            ->where('due_date', '>', today())
            ->orderBy('due_date')
            ->get();

        $completedToday = Task::whereDate('completed_at', today())
            ->latest('completed_at')
            ->get();

        $achievements = \App\Models\Achievement::with('unlocked')->get();

        return view('tasks.index', compact('stats', 'pendingTasks', 'overdueTasks', 'upcomingTasks', 'completedToday', 'achievements'));
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        Task::create($request->validated());

        return redirect()->route('tasks.index')->with('success', 'Taak toegevoegd.');
    }

    public function complete(Task $task, PlayerStatsService $playerStats): RedirectResponse
    {
        $task->update(['completed_at' => now()]);

        $result = $playerStats->recordCompletion($task);

        if ($task->type !== 'once') {
            Task::create([
                'title' => $task->title,
                'description' => $task->description,
                'category' => $task->category,
                'difficulty' => $task->difficulty,
                'type' => $task->type,
                'due_date' => $task->type === 'daily' ? today()->addDay() : today()->addWeek(),
            ]);
        }

        $message = "+{$result['earned_xp']} XP verdiend!";
        if ($result['leveled_up']) {
            $message .= ' 🎉 Level up!';
        }

        return redirect()->route('tasks.index')
            ->with('xp_earned', $result['earned_xp'])
            ->with('leveled_up', $result['leveled_up'])
            ->with('new_achievements', $result['new_achievements'])
            ->with('success', $message);
    }

    public function uncomplete(Task $task, PlayerStatsService $playerStats): RedirectResponse
    {
        $task->update(['completed_at' => null]);

        $playerStats->undoCompletion($task);

        return redirect()->route('tasks.index')->with('success', 'Taak teruggezet.');
    }

    public function resetProgress(): RedirectResponse
    {
        \App\Models\UnlockedAchievement::truncate();
        \App\Models\PlayerStats::instance()->update([
            'total_xp' => 0,
            'level' => 1,
            'current_streak' => 0,
            'longest_streak' => 0,
            'tasks_completed' => 0,
            'last_active_date' => null,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Voortgang gereset.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Taak verwijderd.');
    }
}
