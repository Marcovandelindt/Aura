<?php

namespace App\Http\Controllers\Strava;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\PlayedTrack;
use App\Services\Strava\StravaAuthService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StravaController extends Controller
{
    public function __construct(private readonly StravaAuthService $authService) {}

    public function index(Request $request): View
    {
        $isConnected = $this->authService->isConnected();

        $types = Activity::query()->distinct()->orderBy('sport_type')->pluck('sport_type');

        $query = Activity::query()->orderByDesc('start_date');

        if ($request->filled('type')) {
            $query->where('sport_type', $request->get('type'));
        }

        $activities = $query->paginate(25)->withQueryString();

        $stats = [
            'total_activities' => Activity::count(),
            'total_distance_km' => round(Activity::sum('distance') / 1000, 1),
            'total_moving_time' => Activity::sum('moving_time'),
            'total_elevation' => round(Activity::sum('total_elevation_gain')),
        ];

        return view('strava.index', compact('isConnected', 'activities', 'stats', 'types'));
    }

    public function heatmap(): View
    {
        $polylines = Activity::whereNotNull('map_polyline')
            ->pluck('map_polyline');

        return view('strava.heatmap', compact('polylines'));
    }

    public function show(Activity $activity): View
    {
        $endDate = $activity->start_date->addSeconds($activity->elapsed_time);

        $tracks = PlayedTrack::whereBetween('played_at', [$activity->start_date, $endDate])
            ->orderByDesc('played_at')
            ->get();

        return view('strava.show', compact('activity', 'tracks', 'endDate'));
    }
}
