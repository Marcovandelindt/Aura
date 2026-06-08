<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use App\Http\Requests\Social\UpdateScambaiterSettingsRequest;
use App\Models\ScambaiterProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ScambaiterSettingsController extends Controller
{
    public function edit(): View
    {
        $profile = ScambaiterProfile::getInstance();

        return view('scambaiter.settings', compact('profile'));
    }

    public function update(UpdateScambaiterSettingsRequest $request): RedirectResponse
    {
        $profile = ScambaiterProfile::getInstance();
        $profile->update($request->validated());

        return redirect()->route('scambaiter.settings')
            ->with('success', 'Profile saved.');
    }
}
