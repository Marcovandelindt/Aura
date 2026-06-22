<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\EpisodeWatch;
use App\Models\Expense;
use App\Models\HealthEntry;
use App\Models\JournalEntry;
use App\Models\MovieWatch;
use App\Models\NintendoSwitchSession;
use App\Models\Play;
use App\Models\PlayStationSession;
use App\Models\Task;
use App\Models\UnlockedAchievement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnThisDayController extends Controller
{
    public function index(Request $request): View
    {
        $referenceDate = $request->date('datum') ?? today();

        $memories = collect();

        for ($yearsAgo = 1; $yearsAgo <= 5; $yearsAgo++) {
            $date = $referenceDate->copy()->subYears($yearsAgo);
            $memory = $this->gatherForDate($date, $yearsAgo);

            if ($memory['hasData']) {
                $memories->push($memory);
            }
        }

        return view('on-this-day.index', compact('memories', 'referenceDate'));
    }

    private function gatherForDate(Carbon $date, int $yearsAgo): array
    {
        $dateStr = $date->toDateString();

        $plays = Play::with(['track.artists'])
            ->whereDate('played_at', $date)
            ->orderBy('played_at')
            ->get();

        $topTracks = $plays
            ->groupBy('track_id')
            ->map(fn ($group) => [
                'track' => $group->first()->track,
                'count' => $group->count(),
                'last_played_at' => $group->last()->played_at,
            ])
            ->sortByDesc('last_played_at')
            ->take(5)
            ->values();

        $activities = Activity::whereDate('start_date', $date)->get();

        $health = HealthEntry::where('date', $dateStr)->first();

        $psSessions = PlayStationSession::with('game')
            ->whereDate('started_at', $date)
            ->get();

        $nsSessions = NintendoSwitchSession::with('game')
            ->where('started_at', $dateStr)
            ->get();

        $achievements = UnlockedAchievement::with('achievement')
            ->whereDate('unlocked_at', $date)
            ->get();

        $journalEntries = JournalEntry::with('mood')
            ->where('date', $dateStr)
            ->get();

        $movieWatches = MovieWatch::with('movie')
            ->whereDate('watched_at', $date)
            ->where('year_only', false)
            ->get();

        $episodeWatches = EpisodeWatch::with(['episode.season.series'])
            ->whereDate('watched_at', $date)
            ->where('year_only', false)
            ->get();

        $completedTasks = Task::whereDate('completed_at', $date)->get();

        $expenses = Expense::with('expenseCategory')
            ->where('date', $dateStr)
            ->get();

        $hasData = $plays->isNotEmpty()
            || $activities->isNotEmpty()
            || $health !== null
            || $psSessions->isNotEmpty()
            || $nsSessions->isNotEmpty()
            || $achievements->isNotEmpty()
            || $journalEntries->isNotEmpty()
            || $movieWatches->isNotEmpty()
            || $episodeWatches->isNotEmpty()
            || $completedTasks->isNotEmpty()
            || $expenses->isNotEmpty();

        return [
            'date' => $date,
            'yearsAgo' => $yearsAgo,
            'label' => $yearsAgo === 1 ? '1 jaar geleden' : "{$yearsAgo} jaar geleden",
            'hasData' => $hasData,
            'plays' => $plays,
            'topTracks' => $topTracks,
            'uniqueTrackCount' => $plays->pluck('track_id')->unique()->count(),
            'activities' => $activities,
            'health' => $health,
            'psSessions' => $psSessions,
            'nsSessions' => $nsSessions,
            'achievements' => $achievements,
            'journalEntries' => $journalEntries,
            'movieWatches' => $movieWatches,
            'episodeWatches' => $episodeWatches,
            'completedTasks' => $completedTasks,
            'expenses' => $expenses,
        ];
    }
}
