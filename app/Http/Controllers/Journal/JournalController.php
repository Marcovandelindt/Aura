<?php

namespace App\Http\Controllers\Journal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Journal\StoreJournalEntryRequest;
use App\Http\Requests\Journal\UpdateJournalEntryRequest;
use App\Models\Activity;
use App\Models\HealthEntry;
use App\Models\JournalEntry;
use App\Models\JournalTag;
use App\Models\Mood;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JournalController extends Controller
{
    public function index(Request $request): View
    {
        $query = JournalEntry::query()->with(['mood', 'tags'])->orderByDesc('date');

        if ($request->filled('mood')) {
            $query->where('mood_id', $request->mood);
        }

        if ($request->filled('rating_min')) {
            $query->where('rating', '>=', $request->rating_min);
        }

        if ($request->filled('rating_max')) {
            $query->where('rating', '<=', $request->rating_max);
        }

        if ($request->filled('tag')) {
            $query->whereHas('tags', fn ($q) => $q->where('journal_tags.id', $request->tag));
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                    ->orWhere('content', 'like', "%{$request->search}%");
            });
        }

        $entries = $query->paginate(15)->withQueryString();

        $currentMonth = now()->startOfMonth();
        if ($request->filled('month')) {
            $currentMonth = \Carbon\Carbon::createFromFormat('Y-m', $request->month)->startOfMonth();
        }

        $monthlyEntries = JournalEntry::forMonth($currentMonth->year, $currentMonth->month)
            ->get()
            ->keyBy(fn ($e) => $e->date->format('Y-m-d'));

        $streak = JournalEntry::calculateStreak();
        $moods = Mood::active()->orderBy('name')->get();
        $tags = JournalTag::orderBy('name')->get();
        $totalCount = JournalEntry::count();

        return view('journal.index', compact(
            'entries', 'currentMonth', 'monthlyEntries', 'streak', 'moods', 'tags', 'totalCount'
        ));
    }

    public function show(JournalEntry $journalEntry): View
    {
        $journalEntry->load(['mood', 'tags']);

        $healthEntry = HealthEntry::whereDate('date', $journalEntry->date)->first();

        $stravaActivities = Activity::whereDate('start_date_local', $journalEntry->date)->get();

        $previousEntry = JournalEntry::where('date', '<', $journalEntry->date)
            ->orderByDesc('date')
            ->first();

        $nextEntry = JournalEntry::where('date', '>', $journalEntry->date)
            ->orderBy('date')
            ->first();

        return view('journal.show', compact('journalEntry', 'healthEntry', 'stravaActivities', 'previousEntry', 'nextEntry'));
    }

    public function create(): View
    {
        $moods = Mood::active()->orderBy('name')->get();
        $tags = JournalTag::orderBy('name')->get();

        return view('journal.create', compact('moods', 'tags'));
    }

    public function store(StoreJournalEntryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['word_count'] = str_word_count(strip_tags($data['content'] ?? ''));

        $entry = JournalEntry::create(\Arr::except($data, ['tags']));
        $entry->tags()->sync($data['tags'] ?? []);

        return redirect()->route('journal.show', $entry)
            ->with('success', 'Entry opgeslagen.');
    }

    public function edit(JournalEntry $journalEntry): View
    {
        $journalEntry->load(['mood', 'tags']);
        $moods = Mood::active()->orderBy('name')->get();
        $tags = JournalTag::orderBy('name')->get();

        return view('journal.edit', compact('journalEntry', 'moods', 'tags'));
    }

    public function update(UpdateJournalEntryRequest $request, JournalEntry $journalEntry): RedirectResponse
    {
        $data = $request->validated();
        $data['word_count'] = str_word_count(strip_tags($data['content'] ?? ''));

        $journalEntry->update(\Arr::except($data, ['tags']));
        $journalEntry->tags()->sync($data['tags'] ?? []);

        return redirect()->route('journal.show', $journalEntry)
            ->with('success', 'Entry bijgewerkt.');
    }

    public function destroy(JournalEntry $journalEntry): RedirectResponse
    {
        $journalEntry->delete();

        return redirect()->route('journal.index')
            ->with('success', 'Entry verwijderd.');
    }
}
