<?php

namespace App\Http\Controllers\Strava;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Strava\StravaAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StravaAuthController extends Controller
{
    public function __construct(private readonly StravaAuthService $stravaAuthService) {}

    public function redirect(): RedirectResponse
    {
        return $this->stravaAuthService->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()->route('home.index')->with('error', 'Strava authentication was cancelled.');
        }

        try {
            $tokenData = $this->stravaAuthService->handleCallback($request->get('code'));

            Setting::set('strava_access_token', $tokenData['access_token']);
            Setting::set('strava_refresh_token', $tokenData['refresh_token']);
            Setting::set('strava_token_expires_at', $tokenData['expires_at']);
            Setting::set('strava_athlete_id', $tokenData['athlete']['id']);

            return redirect()->route('strava.index')->with('success', 'Successfully connected to Strava!');
        } catch (\Exception $e) {
            Log::error('Strava authentication error: '.$e->getMessage());

            return redirect()->route('home.index')->with('error', 'Failed to authenticate with Strava.');
        }
    }
}
