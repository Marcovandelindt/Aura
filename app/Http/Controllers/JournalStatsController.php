<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\JournalTag;
use Illuminate\View\View;

class JournalStatsController extends Controller
{
    public function index(): View
    {
        $totalEntries = JournalEntry::count();
        $averageRating = JournalEntry::whereNotNull('rating')->avg('rating');
        $totalWords = JournalEntry::sum('word_count');
        $streak = JournalEntry::calculateStreak();

        $ratingsByMonth = JournalEntry::query()
            ->whereNotNull('rating')
            ->selectRaw('YEAR(date) as year, MONTH(date) as month, ROUND(AVG(rating), 1) as avg_rating, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $moodDistribution = JournalEntry::query()
            ->whereNotNull('mood_id')
            ->with('mood')
            ->selectRaw('mood_id, COUNT(*) as count')
            ->groupBy('mood_id')
            ->orderByDesc('count')
            ->get();

        $topTags = JournalTag::withCount('entries')
            ->having('entries_count', '>', 0)
            ->orderByDesc('entries_count')
            ->limit(10)
            ->get();

        $entriesByMonth = JournalEntry::query()
            ->selectRaw('YEAR(date) as year, MONTH(date) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return view('journal.stats', compact(
            'totalEntries',
            'averageRating',
            'totalWords',
            'streak',
            'ratingsByMonth',
            'moodDistribution',
            'topTags',
            'entriesByMonth'
        ));
    }
}
