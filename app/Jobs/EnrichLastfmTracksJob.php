<?php

namespace App\Jobs;

use App\Services\Lastfm\LastfmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnrichLastfmTracksJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 55;

    public int $tries = 3;

    private const TRACKS_PER_BATCH = 80;

    public function __construct(
        private readonly int $enrichedSoFar = 0,
        private readonly int $totalToEnrich = 0,
    ) {}

    public function handle(LastfmService $lastfm): void
    {
        $enriched = $this->enrichedSoFar;
        $total = $this->totalToEnrich;

        if ($total === 0) {
            $total = (int) DB::selectOne(
                'SELECT COUNT(*) as count FROM (SELECT DISTINCT track_name, artist_name FROM lastfm_scrobbles WHERE enriched_at IS NULL) as t'
            )->count;
        }

        Cache::put('lastfm_enrich_status', [
            'running' => true,
            'enriched' => $enriched,
            'total' => $total,
            'error' => null,
        ], now()->addHours(4));

        $tracks = DB::table('lastfm_scrobbles')
            ->whereNull('enriched_at')
            ->select('track_name', 'artist_name')
            ->groupBy('track_name', 'artist_name')
            ->limit(self::TRACKS_PER_BATCH)
            ->get();

        if ($tracks->isEmpty()) {
            Cache::put('lastfm_enrich_status', [
                'running' => false,
                'enriched' => $enriched,
                'total' => $total,
                'remaining' => 0,
                'error' => null,
            ], now()->addHours(4));

            Log::info('Last.fm enrichment: nothing left to process');

            return;
        }

        foreach ($tracks as $track) {
            $durationMs = $lastfm->getTrackInfo($track->track_name, $track->artist_name);

            DB::affectingStatement('
                DELETE s1 FROM lastfm_scrobbles s1
                INNER JOIN lastfm_scrobbles s2
                    ON s1.track_name = s2.track_name
                    AND s1.artist_name = s2.artist_name
                    AND s1.played_at = s2.played_at
                    AND s1.id > s2.id
                WHERE s1.track_name = ? AND s1.artist_name = ?
            ', [$track->track_name, $track->artist_name]);

            DB::table('lastfm_scrobbles')
                ->where('track_name', $track->track_name)
                ->where('artist_name', $track->artist_name)
                ->whereNull('enriched_at')
                ->update(['duration_ms' => $durationMs, 'enriched_at' => now(), 'updated_at' => now()]);

            $enriched++;

            usleep(500_000);
        }

        $remaining = (int) (DB::selectOne(
            'SELECT COUNT(*) as count FROM (SELECT DISTINCT track_name, artist_name FROM lastfm_scrobbles WHERE enriched_at IS NULL) as t'
        )->count);

        Cache::put('lastfm_enrich_status', [
            'running' => false,
            'enriched' => $enriched,
            'total' => $total,
            'remaining' => $remaining,
            'error' => null,
        ], now()->addHours(4));

        Log::info("Last.fm enrichment batch done: {$enriched} processed this run, {$remaining} remaining");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Last.fm enrichment failed: {$exception->getMessage()}");

        $status = Cache::get('lastfm_enrich_status', []);

        Cache::put('lastfm_enrich_status', array_merge($status, [
            'running' => false,
            'error' => "Mislukt: {$exception->getMessage()}",
        ]), now()->addHours(4));
    }
}
