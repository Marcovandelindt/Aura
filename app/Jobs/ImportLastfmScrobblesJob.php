<?php

namespace App\Jobs;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Play;
use App\Models\Track;
use App\Services\Lastfm\LastfmService;
use Carbon\Carbon;
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

        // Only import scrobbles before Spotify tracking started to prevent duplicates
        $firstSpotifyPlay = Play::where('source', 'spotify')->min('played_at');

        $this->updateStatus(running: true, page: $this->startPage, imported: $imported, skipped: $skipped);

        for ($page = $this->startPage; $page <= $endPage; $page++) {
            $result = $lastfm->getRecentTracksPage($page);
            $totalPages = $result['totalPages'];

            foreach ($result['tracks'] as $scrobble) {
                if (isset($scrobble['@attr']['nowplaying'])) {
                    continue;
                }

                $playedAt = isset($scrobble['date']['uts'])
                    ? Carbon::createFromTimestamp((int) $scrobble['date']['uts'])
                    : null;

                if (! $playedAt) {
                    continue;
                }

                if ($firstSpotifyPlay && $playedAt >= $firstSpotifyPlay) {
                    $skipped++;

                    continue;
                }

                $trackName = $scrobble['name'] ?? '';
                $artistName = is_array($scrobble['artist'])
                    ? ($scrobble['artist']['#text'] ?? '')
                    : ($scrobble['artist'] ?? '');
                $albumName = is_array($scrobble['album']) ? ($scrobble['album']['#text'] ?? null) : null;

                if (! $trackName || ! $artistName) {
                    continue;
                }

                $albumImage = null;
                if (! empty($scrobble['image'])) {
                    $images = collect($scrobble['image']);
                    $albumImage = $images->last()['#text'] ?? null;
                    if (empty($albumImage)) {
                        $albumImage = $images->firstWhere('#text', '!=', '')['#text'] ?? null;
                    }
                }

                $track = $this->findOrCreateTrack($trackName, $artistName, $albumName, $albumImage ?: null);

                $inserted = Play::insertOrIgnore([[
                    'track_id' => $track->id,
                    'played_at' => $playedAt,
                    'source' => 'lastfm',
                    'context' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]]);

                $inserted ? $imported++ : $skipped++;
            }

            usleep(250_000);

            $this->updateStatus(running: true, page: $page, totalPages: $totalPages, imported: $imported, skipped: $skipped);

            Log::info("Last.fm import: page {$page}/{$totalPages}, imported {$imported}, skipped {$skipped}");

            if ($page >= $totalPages) {
                break;
            }
        }

        $nextPage = $endPage + 1;

        if ($totalPages !== null && $nextPage <= $totalPages) {
            self::dispatch($nextPage, $imported, $skipped);
        } else {
            $this->updateStatus(running: false, page: $totalPages, totalPages: $totalPages, imported: $imported, skipped: $skipped);
            Log::info("Last.fm import complete: {$imported} imported, {$skipped} skipped");
        }
    }

    private function findOrCreateTrack(string $title, string $artistName, ?string $albumName, ?string $albumImage): Track
    {
        $track = Track::query()
            ->whereRaw('LOWER(title) = ?', [mb_strtolower($title)])
            ->whereHas('artists', fn ($q) => $q->whereRaw('LOWER(name) = ?', [mb_strtolower($artistName)]))
            ->first();

        if ($track) {
            return $track;
        }

        $album = $albumName
            ? Album::firstOrCreate(['name' => $albumName], ['image_url' => $albumImage])
            : null;

        $track = Track::create(['title' => $title, 'album_id' => $album?->id]);

        $artist = Artist::firstOrCreate(['name' => $artistName]);
        $track->artists()->attach($artist->id, ['is_primary' => true, 'sort_order' => 0]);

        return $track;
    }

    private function updateStatus(bool $running, ?int $page = null, ?int $totalPages = null, int $imported = 0, int $skipped = 0, ?string $error = null): void
    {
        Cache::put('lastfm_import_status', [
            'running' => $running,
            'page' => $page,
            'total_pages' => $totalPages ?? Cache::get('lastfm_import_status')['total_pages'] ?? null,
            'imported' => $imported,
            'skipped' => $skipped,
            'error' => $error,
        ], now()->addHours(2));
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Last.fm import failed on page {$this->startPage}: {$exception->getMessage()}");

        $this->updateStatus(
            running: false,
            page: $this->startPage,
            imported: $this->totalImportedSoFar,
            skipped: $this->totalSkippedSoFar,
            error: "Mislukt op pagina {$this->startPage}: {$exception->getMessage()}",
        );
    }
}
