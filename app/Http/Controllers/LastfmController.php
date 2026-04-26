<?php

namespace App\Http\Controllers;

use App\Jobs\ImportLastfmScrobblesJob;
use App\Models\LastfmScrobble;
use App\Services\Lastfm\LastfmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class LastfmController extends Controller
{
    public function __construct(private readonly LastfmService $lastfm) {}

    public function index(): View
    {
        $isConfigured = $this->lastfm->isConfigured();
        $importStatus = Cache::get('lastfm_import_status');
        $totalImported = LastfmScrobble::count();

        $totalLastfm = null;
        if ($isConfigured) {
            try {
                $totalLastfm = $this->lastfm->getTotalScrobbles();
            } catch (\Throwable) {
                // Silently fail — page still loads
            }
        }

        return view('lastfm.index', compact('isConfigured', 'importStatus', 'totalImported', 'totalLastfm'));
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
}
