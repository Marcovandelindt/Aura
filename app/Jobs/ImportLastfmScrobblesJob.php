<?php

namespace App\Jobs;

use App\Models\LastfmScrobble;
use App\Services\Lastfm\LastfmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ImportLastfmScrobblesJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 55;

    public int $tries = 3;

    private const PAGES_PER_BATCH = 5;

    public function __construct(
        private readonly int $startPage = 1,
        private readonly int $totalImportedSoFar = 0,
        private readonly int $totalSkippedSoFar = 0,
    ) {}

    public function handle(LastfmService $lastfm): void
    {
        $imported = $this->totalImportedSoFar;
        $skipped = $this->totalSkippedSoFar;
        $endPage = $this->startPage + self::PAGES_PER_BATCH - 1;
        $totalPages = null;

        Cache::put('lastfm_import_status', [
            'running' => true,
            'page' => $this->startPage,
            'total_pages' => Cache::get('lastfm_import_status.total_pages'),
            'imported' => $imported,
            'skipped' => $skipped,
            'error' => null,
        ], now()->addHours(2));

        for ($page = $this->startPage; $page <= $endPage; $page++) {
            $result = $lastfm->getRecentTracksPage($page);
            $totalPages = $result['totalPages'];

            foreach ($result['tracks'] as $track) {
                if (isset($track['@attr']['nowplaying'])) {
                    continue;
                }

                $playedAt = isset($track['date']['uts'])
                    ? \Carbon\Carbon::createFromTimestamp((int) $track['date']['uts'])
                    : null;

                if (! $playedAt) {
                    continue;
                }

                $trackName = $track['name'] ?? '';
                $artistName = is_array($track['artist']) ? ($track['artist']['#text'] ?? '') : ($track['artist'] ?? '');
                $albumName = is_array($track['album']) ? ($track['album']['#text'] ?? null) : null;

                $albumImage = null;
                if (! empty($track['image'])) {
                    $images = collect($track['image']);
                    $albumImage = $images->last()['#text'] ?? null;
                    if (empty($albumImage)) {
                        $albumImage = $images->firstWhere('#text', '!=', '')['#text'] ?? null;
                    }
                }

                try {
                    $scrobble = LastfmScrobble::firstOrCreate(
                        ['track_name' => $trackName, 'artist_name' => $artistName, 'played_at' => $playedAt],
                        ['album_name' => $albumName ?: null, 'album_image_url' => $albumImage ?: null],
                    );

                    $scrobble->wasRecentlyCreated ? $imported++ : $skipped++;
                } catch (\Illuminate\Database\UniqueConstraintViolationException) {
                    $skipped++;
                }
            }

            usleep(250_000);

            Cache::put('lastfm_import_status', [
                'running' => true,
                'page' => $page,
                'total_pages' => $totalPages,
                'imported' => $imported,
                'skipped' => $skipped,
                'error' => null,
            ], now()->addHours(2));

            Log::info("Last.fm import: page {$page}/{$totalPages}, imported {$imported}, skipped {$skipped}");

            if ($page >= $totalPages) {
                break;
            }
        }

        $nextPage = $endPage + 1;

        if ($totalPages !== null && $nextPage <= $totalPages) {
            self::dispatch($nextPage, $imported, $skipped);
        } else {
            Cache::put('lastfm_import_status', [
                'running' => false,
                'page' => $totalPages,
                'total_pages' => $totalPages,
                'imported' => $imported,
                'skipped' => $skipped,
                'error' => null,
            ], now()->addHours(2));

            Log::info("Last.fm import complete: {$imported} imported, {$skipped} skipped");
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Last.fm import failed on page {$this->startPage}: {$exception->getMessage()}");

        Cache::put('lastfm_import_status', [
            'running' => false,
            'page' => $this->startPage,
            'total_pages' => Cache::get('lastfm_import_status')['total_pages'] ?? null,
            'imported' => $this->totalImportedSoFar,
            'skipped' => $this->totalSkippedSoFar,
            'error' => "Mislukt op pagina {$this->startPage}: {$exception->getMessage()}",
        ], now()->addHours(2));
    }
}
