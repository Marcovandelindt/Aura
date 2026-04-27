<?php

namespace App\Http\Controllers;

use App\Models\LastfmScrobble;
use App\Models\PlayedTrack;
use App\Services\Spotify\SpotifyTrackService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MusicController extends Controller
{
    protected SpotifyTrackService $trackService;

    public function __construct(SpotifyTrackService $trackService)
    {
        $this->trackService = $trackService;
    }

    /**
     * Display listening history
     */
    public function index(Request $request): View
    {
        $filter = $request->get('filter', 'all');
        $search = $request->get('search');

        // Last.fm scrobbles are only shown before Spotify tracking started to prevent duplicates
        $firstSpotifyPlay = PlayedTrack::min('played_at');

        $spotifyQuery = DB::table('played_tracks')->select([
            'track_name',
            'artist_names',
            'album_name',
            'album_image_url',
            'played_at',
            'spotify_track_id',
            'preview_url',
            'duration_ms',
            DB::raw("'spotify' as source"),
        ]);

        $lastfmQuery = DB::table('lastfm_scrobbles')->select([
            'track_name',
            DB::raw('JSON_ARRAY(artist_name) as artist_names'),
            'album_name',
            'album_image_url',
            'played_at',
            'spotify_track_id',
            DB::raw('NULL as preview_url'),
            'duration_ms',
            DB::raw("'lastfm' as source"),
        ]);

        if ($firstSpotifyPlay) {
            $lastfmQuery->where('played_at', '<', $firstSpotifyPlay);
        }

        foreach ([$spotifyQuery, $lastfmQuery] as $query) {
            match ($filter) {
                'today' => $query->whereDate('played_at', now()->toDateString()),
                'week' => $query->whereBetween('played_at', [now()->startOfWeek(), now()->endOfWeek()]),
                'month' => $query->whereMonth('played_at', now()->month)->whereYear('played_at', now()->year),
                default => null,
            };
        }

        if ($search) {
            $spotifyQuery->where(function ($q) use ($search) {
                $q->where('track_name', 'like', "%{$search}%")
                    ->orWhere('album_name', 'like', "%{$search}%")
                    ->orWhereJsonContains('artist_names', $search);
            });

            $lastfmQuery->where(function ($q) use ($search) {
                $q->where('track_name', 'like', "%{$search}%")
                    ->orWhere('album_name', 'like', "%{$search}%")
                    ->orWhere('artist_name', 'like', "%{$search}%");
            });
        }

        $tracks = $spotifyQuery->union($lastfmQuery)
            ->orderByDesc('played_at')
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

        // Load moods only for Spotify tracks (require spotify_track_id)
        $spotifyIds = $tracks->filter(fn ($t) => ! empty($t->spotify_track_id))
            ->pluck('spotify_track_id')->unique()->toArray();
        $trackMoods = PlayedTrack::getMoodsForTracks($spotifyIds);
        foreach ($tracks as $track) {
            $track->moods = $trackMoods[$track->spotify_track_id] ?? [];
        }

        $stats = $this->buildCombinedStats($filter, $firstSpotifyPlay);
        $currentlyPlaying = $this->trackService->getCurrentlyPlaying();
        $topArtistThisWeek = $this->getTopArtistThisWeek();

        return view('music.index', compact('tracks', 'stats', 'filter', 'search', 'currentlyPlaying', 'topArtistThisWeek'));
    }

    private function buildCombinedStats(string $filter, ?string $firstSpotifyPlay): array
    {
        $spotifyStats = $this->trackService->getStatistics($filter);

        $lastfmQuery = LastfmScrobble::query();
        if ($firstSpotifyPlay) {
            $lastfmQuery->where('played_at', '<', $firstSpotifyPlay);
        }

        match ($filter) {
            'today' => $lastfmQuery->today(),
            'week' => $lastfmQuery->thisWeek(),
            'month' => $lastfmQuery->thisMonth(),
            default => null,
        };

        $lastfmTotal = $lastfmQuery->count();
        $lastfmDuration = $lastfmQuery->sum('duration_ms');

        return array_merge($spotifyStats, [
            'total_tracks' => $spotifyStats['total_tracks'] + $lastfmTotal,
            'total_duration_ms' => $spotifyStats['total_duration_ms'] + $lastfmDuration,
            'lastfm_total' => $lastfmTotal,
        ]);
    }

    /**
     * Show most played tracks
     */
    public function topTracks(Request $request): View
    {
        $period = $request->get('period', 'all');
        $limit = $request->get('limit', 50);

        $topTracks = PlayedTrack::mostPlayed($limit, $period);

        return view('music.top-tracks', compact('topTracks', 'period'));
    }

    /**
     * Force sync tracks
     */
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

    /**
     * Get top artist this week with stats
     */
    private function getTopArtistThisWeek(): ?object
    {
        $startOfWeek = \Illuminate\Support\Carbon::now()->startOfWeek();
        $endOfWeek = \Illuminate\Support\Carbon::now()->endOfWeek();

        // Get all tracks played this week
        $tracksThisWeek = PlayedTrack::whereBetween('played_at', [$startOfWeek, $endOfWeek])
            ->get();

        if ($tracksThisWeek->isEmpty()) {
            return null;
        }

        // Count plays per artist
        $artistStats = [];
        foreach ($tracksThisWeek as $track) {
            foreach ($track->artist_names as $artistName) {
                if (! isset($artistStats[$artistName])) {
                    $artistStats[$artistName] = [
                        'plays' => 0,
                        'unique_tracks' => [],
                        'total_duration_ms' => 0,
                        'album_images' => [],
                        'first_track' => null,
                        'daily_plays' => [],
                        'hourly_plays' => [],
                        'play_dates' => [],
                    ];
                }

                $artistStats[$artistName]['plays']++;
                $artistStats[$artistName]['unique_tracks'][$track->spotify_track_id] = $track->track_name;
                $artistStats[$artistName]['total_duration_ms'] += $track->duration_ms;

                // Track daily plays
                $dayName = $track->played_at->format('l'); // Monday, Tuesday, etc.
                if (! isset($artistStats[$artistName]['daily_plays'][$dayName])) {
                    $artistStats[$artistName]['daily_plays'][$dayName] = 0;
                }
                $artistStats[$artistName]['daily_plays'][$dayName]++;

                // Track hourly plays (0-23)
                $hour = $track->played_at->format('H');
                if (! isset($artistStats[$artistName]['hourly_plays'][$hour])) {
                    $artistStats[$artistName]['hourly_plays'][$hour] = 0;
                }
                $artistStats[$artistName]['hourly_plays'][$hour]++;

                // Track play dates for streak calculation
                $playDate = $track->played_at->format('Y-m-d');
                if (! in_array($playDate, $artistStats[$artistName]['play_dates'])) {
                    $artistStats[$artistName]['play_dates'][] = $playDate;
                }

                if ($track->album_image_url && ! in_array($track->album_image_url, $artistStats[$artistName]['album_images'])) {
                    $artistStats[$artistName]['album_images'][] = $track->album_image_url;
                }

                if (! $artistStats[$artistName]['first_track']) {
                    $artistStats[$artistName]['first_track'] = $track;
                }
            }
        }

        if (empty($artistStats)) {
            return null;
        }

        // Sort by plays and get top artist
        uasort($artistStats, function ($a, $b) {
            return $b['plays'] <=> $a['plays'];
        });

        $topArtistName = array_key_first($artistStats);
        $topArtistData = $artistStats[$topArtistName];

        // Find the day with most plays
        $topDay = null;
        $maxPlays = 0;
        foreach ($topArtistData['daily_plays'] as $day => $plays) {
            if ($plays > $maxPlays) {
                $maxPlays = $plays;
                $topDay = $day;
            }
        }

        // Calculate peak hour
        $peakHour = null;
        $maxHourlyPlays = 0;
        foreach ($topArtistData['hourly_plays'] as $hour => $plays) {
            if ($plays > $maxHourlyPlays) {
                $maxHourlyPlays = $plays;
                $peakHour = $hour;
            }
        }

        // Calculate streak (consecutive days)
        $playDates = $topArtistData['play_dates'];
        sort($playDates);
        $streak = 0;
        $currentStreak = 1;

        if (count($playDates) > 0) {
            for ($i = 1; $i < count($playDates); $i++) {
                $prevDate = \Illuminate\Support\Carbon::parse($playDates[$i - 1]);
                $currentDate = \Illuminate\Support\Carbon::parse($playDates[$i]);

                if ($currentDate->diffInDays($prevDate) === 1) {
                    $currentStreak++;
                } else {
                    $currentStreak = 1;
                }

                $streak = max($streak, $currentStreak);
            }

            // If only one day, streak is 1
            if (count($playDates) === 1) {
                $streak = 1;
            }
        }

        // Format peak hour for display
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
            'name' => $topArtistName,
            'plays' => $topArtistData['plays'],
            'unique_tracks_count' => count($topArtistData['unique_tracks']),
            'unique_tracks' => array_values($topArtistData['unique_tracks']),
            'total_duration_ms' => $topArtistData['total_duration_ms'],
            'album_image_url' => $topArtistData['album_images'][0] ?? null,
            'sample_track' => $topArtistData['first_track'],
            'top_day' => $topDay,
            'top_day_plays' => $maxPlays,
            'daily_plays' => $topArtistData['daily_plays'],
            'streak_days' => $streak,
            'peak_hour' => $peakHourFormatted,
            'peak_hour_plays' => $maxHourlyPlays,
        ];
    }
}
