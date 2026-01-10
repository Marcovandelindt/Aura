<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GeneralSettingsController extends Controller
{
    public function index(): View
    {
        $settings = [
            'health_step_goal' => Setting::get('health_step_goal', 10000),
        ];

        return view('settings.general', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'health_step_goal' => ['required', 'integer', 'min:1000', 'max:100000'],
        ]);

        Setting::set('health_step_goal', (int) $validated['health_step_goal']);

        return redirect()->route('settings.general')
            ->with('success', 'Settings updated successfully.');
    }
}
