<?php

namespace App\Http\Controllers;

use App\Jobs\EnrichLastfmTracksJob;
use App\Jobs\ImportLastfmScrobblesJob;
use App\Models\LastfmScrobble;
use App\Models\LastfmTrackCorrection;
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
        $totalImported = LastfmScrobble::count();
        $totalEnriched = LastfmScrobble::whereNotNull('enriched_at')->whereNotNull('duration_ms')->distinct()->count(DB::raw('CONCAT(track_name, "|||", artist_name)'));
        $totalUniqueUnenriched = (int) (DB::selectOne('SELECT COUNT(*) as count FROM (SELECT DISTINCT track_name, artist_name FROM lastfm_scrobbles WHERE enriched_at IS NULL) as t')?->count ?? 0);
        $totalUniqueTracks = LastfmScrobble::distinct()->count(DB::raw('CONCAT(track_name, "|||", artist_name)'));

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
        LastfmScrobble::truncate();
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

    public function missingDuration(): View
    {
        $tracks = DB::table('lastfm_scrobbles')
            ->whereNull('duration_ms')
            ->select('track_name', 'artist_name')
            ->selectRaw('COUNT(*) as scrobble_count')
            ->selectRaw('MAX(album_image_url) as album_image_url')
            ->selectRaw('MAX(album_name) as album_name')
            ->selectRaw('MAX(enriched_at) as enriched_at')
            ->groupBy('track_name', 'artist_name')
            ->orderByDesc('scrobble_count')
            ->paginate(50);

        $totalMissing = DB::table('lastfm_scrobbles')->whereNull('duration_ms')->distinct()->count(DB::raw('CONCAT(track_name, "|||", artist_name)'));

        return view('lastfm.missing-duration', compact('tracks', 'totalMissing'));
    }

    public function enrichTrack(Request $request): JsonResponse
    {
        $trackName = $request->input('track_name');
        $artistName = $request->input('artist_name');

        if (! $trackName || ! $artistName) {
            return response()->json(['success' => false, 'message' => 'Track of artiest ontbreekt'], 422);
        }

        $this->deduplicateTrack($trackName, $artistName);

        $durationMs = $this->lastfm->getTrackInfo($trackName, $artistName);

        DB::table('lastfm_scrobbles')
            ->where('track_name', $trackName)
            ->where('artist_name', $artistName)
            ->update(['duration_ms' => $durationMs, 'enriched_at' => now(), 'updated_at' => now()]);

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

        DB::table('lastfm_scrobbles')
            ->where('track_name', $trackName)
            ->where('artist_name', $artistName)
            ->update(['duration_ms' => $durationMs, 'enriched_at' => now(), 'updated_at' => now()]);

        $formatted = sprintf('%d:%02d', $minutes, $seconds);

        return response()->json(['success' => true, 'duration_ms' => $durationMs, 'formatted' => $formatted]);
    }

    public function corrections(): View
    {
        $corrections = LastfmTrackCorrection::query()
            ->orderBy('track_name')
            ->get();

        $correctionIndex = $corrections->keyBy(fn ($c) => $c->track_name.'|||'.$c->artist_name);

        $topTracks = DB::table('lastfm_scrobbles')
            ->select('track_name', 'artist_name')
            ->selectRaw('COUNT(*) as scrobble_count')
            ->groupBy('track_name', 'artist_name')
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

        $tracks = DB::table('lastfm_scrobbles')
            ->where('track_name', 'like', "%{$query}%")
            ->select('track_name', 'artist_name')
            ->selectRaw('COUNT(*) as scrobble_count')
            ->groupBy('track_name', 'artist_name')
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
        $deleted = DB::affectingStatement('
            DELETE s1 FROM lastfm_scrobbles s1
            INNER JOIN lastfm_scrobbles s2
                ON s1.track_name = s2.track_name
                AND s1.artist_name = s2.artist_name
                AND s1.played_at = s2.played_at
                AND s1.id > s2.id
        ');

        return back()->with('success', "Opschonen klaar — {$deleted} dubbele scrobbles verwijderd.");
    }

    private function deduplicateTrack(string $trackName, string $artistName): void
    {
        DB::affectingStatement('
            DELETE s1 FROM lastfm_scrobbles s1
            INNER JOIN lastfm_scrobbles s2
                ON s1.track_name = s2.track_name
                AND s1.artist_name = s2.artist_name
                AND s1.played_at = s2.played_at
                AND s1.id > s2.id
            WHERE s1.track_name = ? AND s1.artist_name = ?
        ', [$trackName, $artistName]);
    }
}
