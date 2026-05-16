<?php

namespace App\Console\Commands;

use App\Jobs\EnrichSpotifyTracks;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Track;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportSpotifyHistory extends Command
{
    protected $signature = 'spotify:import-history
                            {path : A single JSON file or directory containing Spotify JSON export files}
                            {--min-ms=30000 : Minimum milliseconds played to count as a valid listen}
                            {--before= : Only import plays before this date (YYYY-MM-DD), to avoid overlap with the regular sync}
                            {--no-enrich : Skip Spotify API enrichment after import}';

    protected $description = 'Import Spotify extended streaming history from JSON export files';

    public function handle(): int
    {
        $path = $this->argument('path');
        $minMs = (int) $this->option('min-ms');
        $before = $this->option('before');

        $files = $this->resolveFiles($path);

        if ($before) {
            $this->info("Only importing plays before: {$before}");
        }

        if (empty($files)) {
            $this->error('No Streaming_History_Audio_*.json files found at: '.$path);

            return Command::FAILURE;
        }

        $this->info('Found '.count($files).' file(s) to process.');

        $totalImported = 0;
        $totalSkipped = 0;
        $newSpotifyIds = [];

        foreach ($files as $file) {
            $this->line('Processing: '.basename($file));

            $data = json_decode(file_get_contents($file), true);

            if (! $data) {
                $this->warn('  Could not decode file, skipping.');

                continue;
            }

            $records = array_filter($data, fn ($r) => isset($r['spotify_track_uri']) &&
                str_starts_with($r['spotify_track_uri'], 'spotify:track:') &&
                ($r['ms_played'] ?? 0) >= $minMs &&
                (! $before || substr($r['ts'], 0, 10) < $before)
            );

            if (empty($records)) {
                $this->line('  → No valid plays found.');
                unset($data, $records);

                continue;
            }

            // Collect unique track info keyed by Spotify ID
            $uniqueTracks = [];
            foreach ($records as $r) {
                $spotifyId = str_replace('spotify:track:', '', $r['spotify_track_uri']);
                if (! isset($uniqueTracks[$spotifyId])) {
                    $uniqueTracks[$spotifyId] = [
                        'title' => $r['master_metadata_track_name'] ?? 'Unknown',
                        'album_name' => $r['master_metadata_album_album_name'] ?? null,
                        'artist_name' => $r['master_metadata_album_artist_name'] ?? null,
                        'spotify_uri' => $r['spotify_track_uri'],
                    ];
                }
            }

            // Load all already-known tracks in one query
            $existing = Track::query()
                ->whereIn('spotify_track_id', array_keys($uniqueTracks))
                ->pluck('id', 'spotify_track_id')
                ->toArray();

            $trackLookup = $existing;
            $fileNewIds = [];

            foreach ($uniqueTracks as $spotifyId => $info) {
                if (isset($trackLookup[$spotifyId])) {
                    continue;
                }

                $album = null;
                if ($info['album_name']) {
                    $album = Album::firstOrCreate(['name' => $info['album_name']]);
                }

                $track = Track::firstOrCreate(
                    ['spotify_track_id' => $spotifyId],
                    [
                        'title' => $info['title'],
                        'album_id' => $album?->id,
                        'spotify_uri' => $info['spotify_uri'],
                    ]
                );

                if (! $track->artists()->exists() && $info['artist_name']) {
                    $artist = Artist::firstOrCreate(['name' => $info['artist_name']]);
                    $track->artists()->attach($artist->id, ['is_primary' => true, 'sort_order' => 0]);
                }

                $trackLookup[$spotifyId] = $track->id;
                $fileNewIds[] = $spotifyId;
                $newSpotifyIds[] = $spotifyId;
            }

            // Build play rows
            $plays = [];
            foreach ($records as $r) {
                $spotifyId = str_replace('spotify:track:', '', $r['spotify_track_uri']);

                if (! isset($trackLookup[$spotifyId])) {
                    continue;
                }

                $plays[] = [
                    'track_id' => $trackLookup[$spotifyId],
                    'played_at' => $r['ts'],
                    'source' => 'spotify',
                    'context' => json_encode([
                        'platform' => $r['platform'] ?? null,
                        'reason_start' => $r['reason_start'] ?? null,
                        'reason_end' => $r['reason_end'] ?? null,
                        'shuffle' => $r['shuffle'] ?? null,
                        'skipped' => $r['skipped'] ?? null,
                        'ms_played' => $r['ms_played'],
                    ]),
                    'from_import' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $inserted = 0;
            foreach (array_chunk($plays, 500) as $chunk) {
                $inserted += DB::table('plays')->insertOrIgnore($chunk);
            }

            $fileSkipped = count($plays) - $inserted;
            $totalImported += $inserted;
            $totalSkipped += $fileSkipped;

            $knownCount = count($existing);
            $newCount = count($fileNewIds);

            $this->line("  → {$inserted} plays imported, {$fileSkipped} already existed (skipped).");
            $this->line("  → Tracks: {$knownCount} already in DB, {$newCount} new stubs created.");

            unset($data, $records, $uniqueTracks, $plays, $existing, $trackLookup, $fileNewIds);
        }

        $this->newLine();
        $this->info("Import complete. {$totalImported} plays imported, {$totalSkipped} duplicates skipped.");

        if ($this->option('no-enrich')) {
            $this->line('Enrichment skipped (--no-enrich). Run without this flag to enrich tracks via Spotify API.');

            return Command::SUCCESS;
        }

        $newSpotifyIds = array_unique($newSpotifyIds);

        if (empty($newSpotifyIds)) {
            $this->info('No new tracks to enrich.');

            return Command::SUCCESS;
        }

        $batchCount = (int) ceil(count($newSpotifyIds) / 50);

        $this->newLine();
        $this->line(count($newSpotifyIds).' new tracks need Spotify API enrichment.');
        $this->line("This will dispatch {$batchCount} jobs (50 tracks per API call).");

        if (! $this->confirm('Dispatch enrichment jobs now?', true)) {
            $this->line('Enrichment skipped. Re-run with the same path to enrich later, or use --no-enrich to suppress this prompt.');

            return Command::SUCCESS;
        }

        foreach (array_chunk($newSpotifyIds, 50) as $batch) {
            EnrichSpotifyTracks::dispatch($batch);
        }

        $this->info("{$batchCount} enrichment jobs dispatched. Run `php artisan queue:listen` to process them.");

        return Command::SUCCESS;
    }

    /** @return string[] */
    private function resolveFiles(string $path): array
    {
        $path = trim($path);

        if (is_file($path)) {
            return [$path];
        }

        $files = glob(rtrim($path, '/\\').'/Streaming_History_Audio_*.json') ?: [];
        sort($files);

        return $files;
    }
}
