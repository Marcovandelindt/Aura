<?php

namespace App\Http\Controllers\Strava;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Services\Strava\StravaAuthService;
use Illuminate\View\View;

class StravaController extends Controller
{
    public function __construct(private readonly StravaAuthService $authService) {}

    public function index(): View
    {
        $isConnected = $this->authService->isConnected();

        $activities = Activity::query()
            ->orderByDesc('start_date')
            ->paginate(25);

        $stats = [
            'total_activities' => Activity::count(),
            'total_distance_km' => round(Activity::sum('distance') / 1000, 1),
            'total_moving_time' => Activity::sum('moving_time'),
            'total_elevation' => round(Activity::sum('total_elevation_gain')),
        ];

        return view('strava.index', compact('isConnected', 'activities', 'stats'));
    }

    public function show(Activity $activity): View
    {
        return view('strava.show', compact('activity'));
    }
}
