<?php

namespace App\Jobs;

use App\Models\Track;
use App\Services\Lastfm\LastfmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
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
            // Last.fm-only tracks (no Spotify ID) that still have no duration
            $total = Track::whereNull('spotify_track_id')->whereNull('duration_ms')->count();
        }

        Cache::put('lastfm_enrich_status', [
            'running' => true,
            'enriched' => $enriched,
            'total' => $total,
            'error' => null,
        ], now()->addHours(4));

        $tracks = Track::query()
            ->whereNull('spotify_track_id')
            ->whereNull('duration_ms')
            ->with('artists')
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
            $primaryArtist = $track->primaryArtist;

            if (! $primaryArtist) {
                // Mark as attempted with 0 so it won't be retried endlessly
                $track->update(['duration_ms' => 0]);
                $enriched++;

                continue;
            }

            $durationMs = $lastfm->getTrackInfo($track->title, $primaryArtist->name);

            // 0 is used as a sentinel for "tried but not found" so we don't retry endlessly.
            // The formatted_duration accessor treats 0 the same as null (displays '—').
            $track->update(['duration_ms' => $durationMs ?? 0]);

            $enriched++;

            usleep(500_000);
        }

        $remaining = Track::whereNull('spotify_track_id')->whereNull('duration_ms')->count();

        Cache::put('lastfm_enrich_status', [
            'running' => false,
            'enriched' => $enriched,
            'total' => $total,
            'remaining' => $remaining,
            'error' => null,
        ], now()->addHours(4));

        if ($remaining > 0) {
            self::dispatch($enriched, $total);
        } else {
            Log::info("Last.fm enrichment complete: {$enriched} tracks processed");
        }
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
