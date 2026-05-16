<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MusicReportController extends Controller
{
    public function year(?int $year = null): View
    {
        $year = $year ?? now()->year;

        $availableYears = DB::table('plays')
            ->selectRaw('YEAR(played_at) as year')
            ->groupBy('year')
            ->orderByDesc('year')
            ->pluck('year')
            ->all();

        $stats = $this->buildYearStats($year);

        return view('music.report.year', compact('year', 'availableYears', 'stats'));
    }

    private function buildYearStats(int $year): array
    {
        $start = Carbon::create($year, 1, 1)->startOfDay();
        $end = Carbon::create($year, 12, 31)->endOfDay();

        return [
            'overview' => $this->overview($start, $end),
            'top_tracks' => $this->topTracks($start, $end),
            'top_artists' => $this->topArtists($start, $end),
            'top_albums' => $this->topAlbums($start, $end),
            'by_month' => $this->byMonth($year),
            'peak_hour' => $this->peakHour($start, $end),
            'most_active_day' => $this->mostActiveDay($start, $end),
            'first_track' => $this->boundaryTrack($start, $end, 'asc'),
            'last_track' => $this->boundaryTrack($start, $end, 'desc'),
            'new_tracks' => $this->newDiscoveries($start, $end),
        ];
    }

    private function overview(Carbon $start, Carbon $end): array
    {
        $totalPlays = DB::table('plays')->whereBetween('played_at', [$start, $end])->count();
        $uniqueTracks = DB::table('plays')->whereBetween('played_at', [$start, $end])->distinct('track_id')->count('track_id');

        $uniqueArtists = DB::table('plays')
            ->join('track_artists', 'track_artists.track_id', '=', 'plays.track_id')
            ->whereBetween('plays.played_at', [$start, $end])
            ->distinct('track_artists.artist_id')
            ->count('track_artists.artist_id');

        $totalMs = (int) DB::table('plays')
            ->join('tracks', 'tracks.id', '=', 'plays.track_id')
            ->whereBetween('plays.played_at', [$start, $end])
            ->sum('tracks.duration_ms');

        return [
            'total_plays' => $totalPlays,
            'unique_tracks' => $uniqueTracks,
            'unique_artists' => $uniqueArtists,
            'total_hours' => round($totalMs / 1000 / 3600, 1),
            'total_days' => round($totalMs / 1000 / 86400, 1),
        ];
    }

    private function topTracks(Carbon $start, Carbon $end, int $limit = 10): array
    {
        return DB::table('plays')
            ->join('tracks', 'tracks.id', '=', 'plays.track_id')
            ->leftJoin('albums', 'albums.id', '=', 'tracks.album_id')
            ->leftJoin('track_artists', function ($join) {
                $join->on('track_artists.track_id', '=', 'tracks.id')->where('track_artists.is_primary', true);
            })
            ->leftJoin('artists', 'artists.id', '=', 'track_artists.artist_id')
            ->whereBetween('plays.played_at', [$start, $end])
            ->select(
                'tracks.title',
                'tracks.spotify_track_id',
                'albums.image_url as album_image_url',
                DB::raw("COALESCE(artists.name, '') as artist_name"),
            )
            ->selectRaw('COUNT(plays.id) as play_count')
            ->groupBy('tracks.id', 'tracks.title', 'tracks.spotify_track_id', 'albums.image_url', 'artists.name')
            ->orderByDesc('play_count')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => (array) $r)
            ->all();
    }

    private function topArtists(Carbon $start, Carbon $end, int $limit = 10): array
    {
        return DB::table('plays')
            ->join('track_artists', 'track_artists.track_id', '=', 'plays.track_id')
            ->join('artists', 'artists.id', '=', 'track_artists.artist_id')
            ->join('tracks', 'tracks.id', '=', 'plays.track_id')
            ->whereBetween('plays.played_at', [$start, $end])
            ->select('artists.id', 'artists.name', 'artists.image_url')
            ->selectRaw('COUNT(plays.id) as play_count')
            ->selectRaw('COUNT(DISTINCT plays.track_id) as unique_tracks')
            ->selectRaw('SUM(COALESCE(tracks.duration_ms, 0)) as total_ms')
            ->groupBy('artists.id', 'artists.name', 'artists.image_url')
            ->orderByDesc('play_count')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => (array) $r)
            ->all();
    }

    private function topAlbums(Carbon $start, Carbon $end, int $limit = 5): array
    {
        return DB::table('plays')
            ->join('tracks', 'tracks.id', '=', 'plays.track_id')
            ->join('albums', 'albums.id', '=', 'tracks.album_id')
            ->leftJoin('track_artists', function ($join) {
                $join->on('track_artists.track_id', '=', 'tracks.id')->where('track_artists.is_primary', true);
            })
            ->leftJoin('artists', 'artists.id', '=', 'track_artists.artist_id')
            ->whereBetween('plays.played_at', [$start, $end])
            ->select(
                'albums.name as album_name',
                'albums.image_url',
                DB::raw("COALESCE(artists.name, '') as artist_name"),
            )
            ->selectRaw('COUNT(plays.id) as play_count')
            ->selectRaw('COUNT(DISTINCT plays.track_id) as unique_tracks')
            ->groupBy('albums.id', 'albums.name', 'albums.image_url', 'artists.name')
            ->orderByDesc('play_count')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => (array) $r)
            ->all();
    }

    private function byMonth(int $year): array
    {
        $rows = DB::table('plays')
            ->whereYear('played_at', $year)
            ->selectRaw('MONTH(played_at) as month, COUNT(*) as play_count')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('play_count', 'month')
            ->all();

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = [
                'label' => Carbon::create($year, $m, 1)->format('M'),
                'play_count' => $rows[$m] ?? 0,
            ];
        }

        return $months;
    }

    private function peakHour(Carbon $start, Carbon $end): ?array
    {
        $row = DB::table('plays')
            ->whereBetween('played_at', [$start, $end])
            ->selectRaw('HOUR(DATE_ADD(played_at, INTERVAL 1 HOUR)) as hour, COUNT(*) as play_count')
            ->groupBy('hour')
            ->orderByDesc('play_count')
            ->first();

        if (! $row) {
            return null;
        }

        $hour = (int) $row->hour;

        return [
            'hour' => $hour,
            'label' => sprintf('%02d:00 – %02d:00', $hour, ($hour + 1) % 24),
            'play_count' => $row->play_count,
        ];
    }

    private function mostActiveDay(Carbon $start, Carbon $end): ?array
    {
        $row = DB::table('plays')
            ->whereBetween('played_at', [$start, $end])
            ->selectRaw('DATE(played_at) as date, COUNT(*) as play_count')
            ->groupBy('date')
            ->orderByDesc('play_count')
            ->first();

        if (! $row) {
            return null;
        }

        return [
            'date' => Carbon::parse($row->date)->format('l j F'),
            'play_count' => $row->play_count,
        ];
    }

    private function boundaryTrack(Carbon $start, Carbon $end, string $direction): ?array
    {
        $row = DB::table('plays')
            ->join('tracks', 'tracks.id', '=', 'plays.track_id')
            ->leftJoin('albums', 'albums.id', '=', 'tracks.album_id')
            ->leftJoin('track_artists', function ($join) {
                $join->on('track_artists.track_id', '=', 'tracks.id')->where('track_artists.is_primary', true);
            })
            ->leftJoin('artists', 'artists.id', '=', 'track_artists.artist_id')
            ->whereBetween('plays.played_at', [$start, $end])
            ->select(
                'tracks.title',
                'tracks.spotify_track_id',
                'albums.image_url as album_image_url',
                'plays.played_at',
                DB::raw("COALESCE(artists.name, '') as artist_name"),
            )
            ->orderBy('plays.played_at', $direction)
            ->first();

        if (! $row) {
            return null;
        }

        return [
            'title' => $row->title,
            'artist_name' => $row->artist_name,
            'album_image_url' => $row->album_image_url,
            'spotify_track_id' => $row->spotify_track_id,
            'played_at' => Carbon::parse($row->played_at)->setTimezone('Europe/Amsterdam')->format('j F, H:i'),
        ];
    }

    private function newDiscoveries(Carbon $start, Carbon $end): int
    {
        return DB::table(DB::raw('(SELECT track_id, MIN(played_at) as first_play FROM plays GROUP BY track_id) as first_plays'))
            ->whereBetween('first_play', [$start, $end])
            ->count();
    }
}
