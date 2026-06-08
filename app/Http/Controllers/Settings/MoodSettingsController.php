<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Mood;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MoodSettingsController extends Controller
{
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

    public function destroy(Mood $mood): RedirectResponse
    {
        $mood->tracks()->detach();
        $mood->delete();

        return redirect()->route('settings.moods')->with('success', 'Mood deleted successfully!');
    }

    public function toggle(Mood $mood): RedirectResponse
    {
        $mood->update(['is_active' => ! $mood->is_active]);

        $status = $mood->is_active ? 'activated' : 'deactivated';

        return redirect()->route('settings.moods')->with('success', "Mood {$status} successfully!");
    }
}
