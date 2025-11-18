<?php

namespace App\Http\Controllers;

use App\Models\Mood;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MoodSettingsController extends Controller
{
    /**
     * Display the mood settings page
     */
    public function index(): View
    {
        $moods = Mood::orderBy('name')->get();

        $stats = [
            'total' => $moods->count(),
            'active' => $moods->where('is_active', true)->count(),
            'inactive' => $moods->where('is_active', false)->count(),
            'total_usage' => $moods->sum('usage_count'),
        ];

        return view('settings.moods', compact('moods', 'stats'));
    }

    /**
     * Store a new mood
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:moods,name',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
        ]);

        Mood::create([
            'name' => $validated['name'],
            'color' => $validated['color'],
            'icon' => $validated['icon'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
            'usage_count' => 0,
        ]);

        return redirect()->route('settings.moods')->with('success', 'Mood created successfully!');
    }

    /**
     * Update an existing mood
     */
    public function update(Request $request, Mood $mood): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:moods,name,'.$mood->id,
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $mood->update([
            'name' => $validated['name'],
            'color' => $validated['color'],
            'icon' => $validated['icon'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('settings.moods')->with('success', 'Mood updated successfully!');
    }

    /**
     * Delete a mood
     */
    public function destroy(Mood $mood): RedirectResponse
    {
        // Remove all track associations first
        $mood->tracks()->detach();

        $mood->delete();

        return redirect()->route('settings.moods')->with('success', 'Mood deleted successfully!');
    }

    /**
     * Toggle mood active status
     */
    public function toggle(Mood $mood): RedirectResponse
    {
        $mood->update(['is_active' => ! $mood->is_active]);

        $status = $mood->is_active ? 'activated' : 'deactivated';

        return redirect()->route('settings.moods')->with('success', "Mood {$status} successfully!");
    }
}
