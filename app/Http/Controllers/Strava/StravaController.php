<?php

namespace App\Http\Controllers\Strava;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Services\Strava\StravaAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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

        $tracks = DB::table('plays')
            ->join('tracks', 'tracks.id', '=', 'plays.track_id')
            ->leftJoin('albums', 'albums.id', '=', 'tracks.album_id')
            ->leftJoin('track_artists', function ($join) {
                $join->on('track_artists.track_id', '=', 'tracks.id')
                    ->where('track_artists.is_primary', true);
            })
            ->leftJoin('artists', 'artists.id', '=', 'track_artists.artist_id')
            ->whereBetween('plays.played_at', [$activity->start_date, $endDate])
            ->select(
                'plays.played_at',
                'tracks.title as track_name',
                'tracks.spotify_track_id',
                'tracks.duration_ms',
                'albums.name as album_name',
                'albums.image_url as album_image_url',
                DB::raw("COALESCE(artists.name, '') as artists_string"),
            )
            ->orderByDesc('plays.played_at')
            ->get()
            ->map(function ($track) {
                $track->played_at = Carbon::parse($track->played_at);
                $track->formatted_duration = $track->duration_ms
                    ? sprintf('%d:%02d', floor($track->duration_ms / 60000), floor(($track->duration_ms % 60000) / 1000))
                    : '--:--';

                return $track;
            });

        return view('strava.show', compact('activity', 'tracks', 'endDate'));
    }
}
