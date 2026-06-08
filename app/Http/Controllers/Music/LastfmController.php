<?php

namespace App\Http\Controllers\Music;

use App\Http\Controllers\Controller;
use App\Jobs\EnrichLastfmTracksJob;
use App\Jobs\ImportLastfmScrobblesJob;
use App\Models\LastfmTrackCorrection;
use App\Models\Play;
use App\Models\Track;
use App\Services\Lastfm\LastfmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LastfmController extends Controller
{
    public function __construct(private readonly LastfmService $lastfm) {}

    public function index(): View
    {
        $isConfigured = $this->lastfm->isConfigured();
        $importStatus = Cache::get('lastfm_import_status');
        $enrichStatus = Cache::get('lastfm_enrich_status');

        $totalImported = Play::where('source', 'lastfm')->count();
        $totalUniqueTracks = Track::whereHas('plays', fn ($q) => $q->where('source', 'lastfm'))->count();
        $totalEnriched = Track::whereHas('plays', fn ($q) => $q->where('source', 'lastfm'))
            ->whereNotNull('duration_ms')
            ->where('duration_ms', '>', 0)
            ->count();
        $totalUniqueUnenriched = Track::whereHas('plays', fn ($q) => $q->where('source', 'lastfm'))
            ->whereNull('duration_ms')
            ->count();

        $totalLastfm = null;
        if ($isConfigured) {
            try {
                $totalLastfm = $this->lastfm->getTotalScrobbles();
            } catch (\Throwable) {
                // Silently fail — page still loads
            }
        }

        return view('lastfm.index', compact('isConfigured', 'importStatus', 'enrichStatus', 'totalImported', 'totalLastfm', 'totalEnriched', 'totalUniqueUnenriched', 'totalUniqueTracks'));
    }

    public function startImport(): RedirectResponse
    {
        if (! $this->lastfm->isConfigured()) {
            return back()->with('error', 'Last.fm is niet geconfigureerd. Voeg LASTFM_API_KEY en LASTFM_USERNAME toe aan je .env.');
        }

        if (Cache::get('lastfm_import_status.running')) {
            return back()->with('error', 'Import is al bezig.');
        }

        ImportLastfmScrobblesJob::dispatch(1, 0, 0);

        return back()->with('success', 'Import gestart!');
    }

    public function importStatus(): JsonResponse
    {
        return response()->json(Cache::get('lastfm_import_status', [
            'running' => false,
            'page' => 0,
            'total_pages' => null,
            'imported' => 0,
            'skipped' => 0,
            'error' => null,
        ]));
    }

    public function clearImport(): RedirectResponse
    {
        Play::where('source', 'lastfm')->delete();
        Cache::forget('lastfm_import_status');

        return back()->with('success', 'Alle Last.fm scrobbles zijn verwijderd.');
    }

    public function startEnrichment(): RedirectResponse
    {
        if (! $this->lastfm->isConfigured()) {
            return back()->with('error', 'Last.fm is niet geconfigureerd.');
        }

        if (Cache::get('lastfm_enrich_status')['running'] ?? false) {
            return back()->with('error', 'Verrijking is al bezig.');
        }

        EnrichLastfmTracksJob::dispatch(0, 0);

        return back()->with('success', 'Verrijking gestart!');
    }

    public function enrichmentStatus(): JsonResponse
    {
        return response()->json(Cache::get('lastfm_enrich_status', [
            'running' => false,
            'enriched' => 0,
            'total' => 0,
            'error' => null,
        ]));
    }

    public function missingDuration(Request $request): View
    {
        $search = $request->input('search');

        $tracks = DB::table('tracks')
            ->join('plays', 'plays.track_id', '=', 'tracks.id')
            ->leftJoin('albums', 'albums.id', '=', 'tracks.album_id')
            ->leftJoin('track_artists', function ($join) {
                $join->on('track_artists.track_id', '=', 'tracks.id')
                    ->where('track_artists.is_primary', true);
            })
            ->leftJoin('artists', 'artists.id', '=', 'track_artists.artist_id')
            ->where('plays.source', 'lastfm')
            ->where(function ($q) {
                $q->whereNull('tracks.duration_ms')->orWhere('tracks.duration_ms', 0);
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('tracks.title', 'like', "%{$search}%")
                        ->orWhere('artists.name', 'like', "%{$search}%");
                });
            })
            ->select(
                'tracks.id',
                'tracks.title as track_name',
                'tracks.duration_ms',
                DB::raw("COALESCE(artists.name, '') as artist_name"),
                'albums.name as album_name',
                'albums.image_url as album_image_url',
            )
            ->selectRaw('COUNT(plays.id) as scrobble_count')
            ->groupBy('tracks.id', 'tracks.title', 'tracks.duration_ms', 'artists.name', 'albums.name', 'albums.image_url')
            ->orderByDesc('scrobble_count')
            ->paginate(50)
            ->appends(['search' => $search]);

        foreach ($tracks as $track) {
            $track->enriched_at = $track->duration_ms !== null ? now() : null;
        }

        $totalMissing = DB::table('tracks')
            ->join('plays', 'plays.track_id', '=', 'tracks.id')
            ->where('plays.source', 'lastfm')
            ->where(function ($q) {
                $q->whereNull('tracks.duration_ms')->orWhere('tracks.duration_ms', 0);
            })
            ->distinct()
            ->count('tracks.id');

        return view('lastfm.missing-duration', compact('tracks', 'totalMissing', 'search'));
    }

    public function enrichTrack(Request $request): JsonResponse
    {
        $trackName = $request->input('track_name');
        $artistName = $request->input('artist_name');

        if (! $trackName || ! $artistName) {
            return response()->json(['success' => false, 'message' => 'Track of artiest ontbreekt'], 422);
        }

        $track = Track::query()
            ->whereRaw('LOWER(title) = ?', [mb_strtolower($trackName)])
            ->whereHas('artists', fn ($q) => $q->whereRaw('LOWER(name) = ?', [mb_strtolower($artistName)]))
            ->first();

        if (! $track) {
            return response()->json(['success' => false, 'message' => 'Track niet gevonden in database']);
        }

        $durationMs = $this->lastfm->getTrackInfo($trackName, $artistName);
        $track->update(['duration_ms' => $durationMs ?? 0]);

        if ($durationMs === null) {
            return response()->json(['success' => false, 'message' => 'Geen duratie gevonden op Last.fm']);
        }

        $seconds = intdiv($durationMs, 1000);
        $formatted = sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60);

        return response()->json(['success' => true, 'duration_ms' => $durationMs, 'formatted' => $formatted]);
    }

    public function setDuration(Request $request): JsonResponse
    {
        $trackName = $request->input('track_name');
        $artistName = $request->input('artist_name');
        $minutes = (int) $request->input('minutes', 0);
        $seconds = (int) $request->input('seconds', 0);

        if (! $trackName || ! $artistName) {
            return response()->json(['success' => false, 'message' => 'Track of artiest ontbreekt'], 422);
        }

        if ($minutes < 0 || $seconds < 0 || $seconds >= 60 || ($minutes === 0 && $seconds === 0)) {
            return response()->json(['success' => false, 'message' => 'Ongeldige duratie'], 422);
        }

        $durationMs = (($minutes * 60) + $seconds) * 1000;

        Track::query()
            ->whereRaw('LOWER(title) = ?', [mb_strtolower($trackName)])
            ->whereHas('artists', fn ($q) => $q->whereRaw('LOWER(name) = ?', [mb_strtolower($artistName)]))
            ->update(['duration_ms' => $durationMs]);

        $formatted = sprintf('%d:%02d', $minutes, $seconds);

        return response()->json(['success' => true, 'duration_ms' => $durationMs, 'formatted' => $formatted]);
    }

    public function corrections(): View
    {
        $corrections = LastfmTrackCorrection::query()
            ->orderBy('track_name')
            ->get();

        $correctionIndex = $corrections->keyBy(fn ($c) => $c->track_name.'|||'.$c->artist_name);

        $topTracks = DB::table('plays')
            ->join('tracks', 'tracks.id', '=', 'plays.track_id')
            ->leftJoin('track_artists', function ($join) {
                $join->on('track_artists.track_id', '=', 'tracks.id')
                    ->where('track_artists.is_primary', true);
            })
            ->leftJoin('artists', 'artists.id', '=', 'track_artists.artist_id')
            ->where('plays.source', 'lastfm')
            ->select(
                'tracks.title as track_name',
                DB::raw("COALESCE(artists.name, '') as artist_name"),
            )
            ->selectRaw('COUNT(plays.id) as scrobble_count')
            ->groupBy('tracks.title', 'artists.name')
            ->orderByDesc('scrobble_count')
            ->limit(100)
            ->get()
            ->map(function ($track) use ($correctionIndex) {
                $key = $track->track_name.'|||'.$track->artist_name;
                $correction = $correctionIndex->get($key);

                return [
                    'track_name' => $track->track_name,
                    'artist_name' => $track->artist_name,
                    'scrobble_count' => $track->scrobble_count,
                    'correction_id' => $correction?->id,
                    'all_artist_names' => $correction?->all_artist_names ?? [$track->artist_name],
                ];
            });

        return view('lastfm.corrections', compact('corrections', 'topTracks'));
    }

    public function saveCorrection(Request $request): JsonResponse
    {
        $trackName = $request->input('track_name');
        $artistName = $request->input('artist_name');
        $allArtistNames = $request->input('all_artist_names', []);

        if (! $trackName || ! $artistName) {
            return response()->json(['success' => false, 'message' => 'Track of artiest ontbreekt'], 422);
        }

        $allArtistNames = array_values(array_filter(array_map('trim', $allArtistNames)));

        if (empty($allArtistNames)) {
            return response()->json(['success' => false, 'message' => 'Voer minimaal één artiest in'], 422);
        }

        $correction = LastfmTrackCorrection::updateOrCreate(
            ['track_name' => $trackName, 'artist_name' => $artistName],
            ['all_artist_names' => $allArtistNames],
        );

        return response()->json(['success' => true, 'id' => $correction->id]);
    }

    public function deleteCorrection(int $id): JsonResponse
    {
        LastfmTrackCorrection::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }

    public function searchTracksForCorrection(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $tracks = DB::table('plays')
            ->join('tracks', 'tracks.id', '=', 'plays.track_id')
            ->leftJoin('track_artists', function ($join) {
                $join->on('track_artists.track_id', '=', 'tracks.id')
                    ->where('track_artists.is_primary', true);
            })
            ->leftJoin('artists', 'artists.id', '=', 'track_artists.artist_id')
            ->where('plays.source', 'lastfm')
            ->where('tracks.title', 'like', "%{$query}%")
            ->select(
                'tracks.title as track_name',
                DB::raw("COALESCE(artists.name, '') as artist_name"),
            )
            ->selectRaw('COUNT(plays.id) as scrobble_count')
            ->groupBy('tracks.title', 'artists.name')
            ->orderByDesc('scrobble_count')
            ->limit(10)
            ->get()
            ->map(function ($track) {
                $correction = LastfmTrackCorrection::where('track_name', $track->track_name)
                    ->where('artist_name', $track->artist_name)
                    ->first();

                return [
                    'track_name' => $track->track_name,
                    'artist_name' => $track->artist_name,
                    'scrobble_count' => $track->scrobble_count,
                    'correction_id' => $correction?->id,
                    'all_artist_names' => $correction?->all_artist_names ?? [$track->artist_name],
                ];
            });

        return response()->json($tracks);
    }

    public function deduplicateAll(): RedirectResponse
    {
        return back()->with('success', 'Opschonen klaar — het nieuwe schema voorkomt duplicaten automatisch.');
    }
}
