<?php

namespace App\Http\Controllers;

use App\Models\Play;
use App\Services\Spotify\SpotifyTrackService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MusicController extends Controller
{
    public function __construct(protected SpotifyTrackService $trackService) {}

    public function index(Request $request): View
    {
        $filter = $request->get('filter', 'all');
        $search = $request->get('search');

        $query = DB::table('plays')
            ->join('tracks', 'tracks.id', '=', 'plays.track_id')
            ->leftJoin('albums', 'albums.id', '=', 'tracks.album_id')
            ->leftJoin('track_artists', function ($join) {
                $join->on('track_artists.track_id', '=', 'tracks.id')
                    ->where('track_artists.is_primary', true);
            })
            ->leftJoin('artists', 'artists.id', '=', 'track_artists.artist_id')
            ->select(
                'plays.id as play_id',
                'plays.played_at',
                'plays.source',
                'tracks.title as track_name',
                'tracks.spotify_track_id',
                'tracks.preview_url',
                'tracks.duration_ms',
                'albums.name as album_name',
                'albums.image_url as album_image_url',
                DB::raw("JSON_ARRAY(COALESCE(artists.name, '')) as artist_names"),
            );

        match ($filter) {
            'today' => $query->whereDate('plays.played_at', now()->toDateString()),
            'week' => $query->whereBetween('plays.played_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereMonth('plays.played_at', now()->month)->whereYear('plays.played_at', now()->year),
            default => null,
        };

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('tracks.title', 'like', "%{$search}%")
                    ->orWhere('albums.name', 'like', "%{$search}%")
                    ->orWhere('artists.name', 'like', "%{$search}%");
            });
        }

        $tracks = $query->orderByDesc('plays.played_at')
            ->paginate(50)
            ->withQueryString()
            ->through(function ($track) {
                $track->artist_names = json_decode($track->artist_names, true) ?? [];
                $track->played_at = Carbon::parse($track->played_at);
                $track->formatted_duration = $track->duration_ms
                    ? sprintf('%d:%02d', floor($track->duration_ms / 60000), floor(($track->duration_ms % 60000) / 1000))
                    : '--:--';
                $track->moods = [];

                return $track;
            });

        $stats = $this->buildStats($filter);
        $currentlyPlaying = $this->trackService->getCurrentlyPlaying();
        $topArtistThisWeek = $this->getTopArtistThisWeek();

        return view('music.index', compact('tracks', 'stats', 'filter', 'search', 'currentlyPlaying', 'topArtistThisWeek'));
    }

    private function buildStats(string $filter): array
    {
        $query = Play::query();

        match ($filter) {
            'today' => $query->whereDate('played_at', today()),
            'week' => $query->whereBetween('played_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereMonth('played_at', now()->month)->whereYear('played_at', now()->year),
            default => null,
        };

        $total = (clone $query)->count();
        $unique = (clone $query)->distinct('track_id')->count('track_id');
        $durationMs = (clone $query)->join('tracks', 'tracks.id', '=', 'plays.track_id')->sum('tracks.duration_ms');
        $dataSince = Play::where('source', 'spotify')->min('played_at');

        return [
            'total_tracks' => $total,
            'unique_tracks' => $unique,
            'total_duration_ms' => $durationMs,
            'data_since' => $dataSince ? Carbon::parse($dataSince) : null,
            'most_played' => [],
        ];
    }

    public function topTracks(Request $request): View
    {
        $period = $request->get('period', 'all');
        $limit = $request->get('limit', 50);

        $query = DB::table('plays')
            ->join('tracks', 'tracks.id', '=', 'plays.track_id')
            ->leftJoin('albums', 'albums.id', '=', 'tracks.album_id')
            ->leftJoin('track_artists', function ($join) {
                $join->on('track_artists.track_id', '=', 'tracks.id')
                    ->where('track_artists.is_primary', true);
            })
            ->leftJoin('artists', 'artists.id', '=', 'track_artists.artist_id')
            ->select(
                'tracks.id',
                'tracks.title as track_name',
                'tracks.spotify_track_id',
                'tracks.duration_ms',
                'albums.name as album_name',
                'albums.image_url as album_image_url',
                DB::raw("COALESCE(artists.name, '') as artists_string"),
            )
            ->selectRaw('COUNT(plays.id) as play_count')
            ->groupBy('tracks.id', 'tracks.title', 'tracks.spotify_track_id', 'tracks.duration_ms', 'albums.name', 'albums.image_url', 'artists.name');

        match ($period) {
            'today' => $query->whereDate('plays.played_at', today()),
            'week' => $query->whereBetween('plays.played_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereMonth('plays.played_at', now()->month)->whereYear('plays.played_at', now()->year),
            default => null,
        };

        $topTracks = $query->orderByDesc('play_count')->limit($limit)->get();

        return view('music.top-tracks', compact('topTracks', 'period'));
    }

    public function syncTracks()
    {
        try {
            $result = $this->trackService->syncRecentlyPlayed();

            return redirect()->route('music.index')
                ->with('success', "Synced {$result['synced']} new tracks!");

        } catch (\Exception $e) {
            return redirect()->route('music.index')
                ->with('error', 'Failed to sync tracks: '.$e->getMessage());
        }
    }

    private function getTopArtistThisWeek(): ?object
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $topArtist = DB::table('plays')
            ->join('track_artists', 'track_artists.track_id', '=', 'plays.track_id')
            ->join('artists', 'artists.id', '=', 'track_artists.artist_id')
            ->join('tracks', 'tracks.id', '=', 'plays.track_id')
            ->leftJoin('albums', 'albums.id', '=', 'tracks.album_id')
            ->whereBetween('plays.played_at', [$startOfWeek, $endOfWeek])
            ->selectRaw('artists.id, artists.name, COUNT(plays.id) as plays')
            ->selectRaw('COUNT(DISTINCT plays.track_id) as unique_tracks_count')
            ->selectRaw('SUM(tracks.duration_ms) as total_duration_ms')
            ->selectRaw('MAX(albums.image_url) as album_image_url')
            ->groupBy('artists.id', 'artists.name')
            ->orderByDesc('plays')
            ->first();

        if (! $topArtist) {
            return null;
        }

        // Compute daily and hourly play breakdown
        $playBreakdown = DB::table('plays')
            ->join('track_artists', 'track_artists.track_id', '=', 'plays.track_id')
            ->where('track_artists.artist_id', $topArtist->id)
            ->whereBetween('plays.played_at', [$startOfWeek, $endOfWeek])
            ->selectRaw('DAYNAME(plays.played_at) as day_name')
            ->selectRaw('HOUR(plays.played_at) as play_hour')
            ->selectRaw('COUNT(*) as play_count')
            ->groupBy('day_name', 'play_hour')
            ->get();

        $dailyPlays = [];
        $hourlyPlays = [];

        foreach ($playBreakdown as $row) {
            $dailyPlays[$row->day_name] = ($dailyPlays[$row->day_name] ?? 0) + $row->play_count;
            $hourlyPlays[$row->play_hour] = ($hourlyPlays[$row->play_hour] ?? 0) + $row->play_count;
        }

        $topDay = ! empty($dailyPlays) ? array_key_first(arsort($dailyPlays) ? $dailyPlays : $dailyPlays) : null;
        arsort($dailyPlays);
        $topDay = array_key_first($dailyPlays);
        $topDayPlays = $dailyPlays[$topDay] ?? 0;

        arsort($hourlyPlays);
        $peakHour = array_key_first($hourlyPlays);
        $peakHourPlays = $hourlyPlays[$peakHour] ?? 0;

        $peakHourFormatted = null;
        if ($peakHour !== null) {
            $hour = (int) $peakHour;
            if ($hour === 0) {
                $peakHourFormatted = '12 AM';
            } elseif ($hour < 12) {
                $peakHourFormatted = $hour.' AM';
            } elseif ($hour === 12) {
                $peakHourFormatted = '12 PM';
            } else {
                $peakHourFormatted = ($hour - 12).' PM';
            }
        }

        return (object) [
            'name' => $topArtist->name,
            'plays' => $topArtist->plays,
            'unique_tracks_count' => $topArtist->unique_tracks_count,
            'unique_tracks' => [],
            'total_duration_ms' => $topArtist->total_duration_ms ?? 0,
            'album_image_url' => $topArtist->album_image_url,
            'sample_track' => null,
            'top_day' => $topDay,
            'top_day_plays' => $topDayPlays,
            'daily_plays' => $dailyPlays,
            'streak_days' => 0,
            'peak_hour' => $peakHourFormatted,
            'peak_hour_plays' => $peakHourPlays,
        ];
    }
}
