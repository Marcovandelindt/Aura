<?php

namespace App\Console\Commands;

use App\Models\Artist;
use App\Models\Track;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateMusicDataCommand extends Command
{
    protected $signature = 'music:migrate-data {--fresh : Clear new tables and start over}';

    protected $description = 'Migrate played_tracks and lastfm_scrobbles into the new normalized music schema';

    // In-memory caches to avoid redundant lookups within the command run
    private array $artistCache = [];

    private array $albumCache = [];

    private array $trackCache = [];

    public function handle(): int
    {
        if ($this->option('fresh')) {
            $this->warn('Clearing new tables...');
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('plays')->truncate();
            DB::table('track_artists')->truncate();
            DB::table('tracks')->truncate();
            DB::table('albums')->truncate();
            DB::table('artists')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->info('Tables cleared.');
        }

        $this->migrateSpotify();
        $this->migrateLastfm();

        $this->newLine();
        $this->info('Migration complete.');
        $this->table(['Table', 'Rows'], [
            ['artists', number_format(DB::table('artists')->count())],
            ['albums', number_format(DB::table('albums')->count())],
            ['tracks', number_format(DB::table('tracks')->count())],
            ['plays', number_format(DB::table('plays')->count())],
        ]);

        return self::SUCCESS;
    }

    private function migrateSpotify(): void
    {
        $this->info('--- Spotify ---');

        $uniqueTracks = DB::table('played_tracks')
            ->select('spotify_track_id', 'track_name', 'artist_names', 'album_name', 'album_image_url', 'duration_ms', 'popularity', 'preview_url', 'spotify_uri')
            ->groupBy('spotify_track_id', 'track_name', 'artist_names', 'album_name', 'album_image_url', 'duration_ms', 'popularity', 'preview_url', 'spotify_uri')
            ->get();

        $bar = $this->output->createProgressBar($uniqueTracks->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $bar->start();

        foreach ($uniqueTracks as $row) {
            $bar->setMessage($row->track_name);

            $artistNames = json_decode($row->artist_names, true) ?? [];
            $albumId = $row->album_name ? $this->findOrCreateAlbum($row->album_name, $row->album_image_url) : null;
            $trackId = $this->findOrCreateTrack(
                title: $row->track_name,
                spotifyTrackId: $row->spotify_track_id,
                albumId: $albumId,
                durationMs: $row->duration_ms,
                popularity: $row->popularity,
                previewUrl: $row->preview_url,
                spotifyUri: $row->spotify_uri,
            );

            $this->syncTrackArtists($trackId, $artistNames);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // Migrate plays
        $totalPlays = DB::table('played_tracks')->count();
        $this->line("Migrating {$totalPlays} Spotify plays...");

        $bar2 = $this->output->createProgressBar($totalPlays);
        $bar2->start();

        DB::table('played_tracks')
            ->select('spotify_track_id', 'played_at', 'contexts')
            ->orderBy('played_at')
            ->chunk(500, function ($rows) use ($bar2) {
                $inserts = [];
                $now = now();

                foreach ($rows as $row) {
                    $trackId = $this->trackCache['spotify:'.$row->spotify_track_id] ?? null;
                    if (! $trackId) {
                        $trackId = DB::table('tracks')->where('spotify_track_id', $row->spotify_track_id)->value('id');
                        $this->trackCache['spotify:'.$row->spotify_track_id] = $trackId;
                    }

                    if (! $trackId) {
                        continue;
                    }

                    $inserts[] = [
                        'track_id' => $trackId,
                        'played_at' => $row->played_at,
                        'source' => 'spotify',
                        'context' => $row->contexts,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($inserts) {
                    DB::table('plays')->insertOrIgnore($inserts);
                }

                $bar2->advance(count($rows));
            });

        $bar2->finish();
        $this->newLine();
        $this->info('Spotify done.');
    }

    private function migrateLastfm(): void
    {
        $this->info('--- Last.fm ---');

        $firstSpotifyPlay = DB::table('played_tracks')->min('played_at');

        if (! $firstSpotifyPlay) {
            $this->warn('No Spotify plays found, importing all Last.fm scrobbles.');
        } else {
            $this->line("Importing Last.fm scrobbles before {$firstSpotifyPlay}");
        }

        $query = DB::table('lastfm_scrobbles');
        if ($firstSpotifyPlay) {
            $query->where('played_at', '<', $firstSpotifyPlay);
        }

        $uniqueTracks = (clone $query)
            ->select('track_name', 'artist_name', 'album_name', 'album_image_url', 'duration_ms', 'spotify_track_id')
            ->groupBy('track_name', 'artist_name', 'album_name', 'album_image_url', 'duration_ms', 'spotify_track_id')
            ->get();

        $bar = $this->output->createProgressBar($uniqueTracks->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $bar->start();

        foreach ($uniqueTracks as $row) {
            $bar->setMessage($row->track_name);

            // Try to match an existing Spotify track first
            $trackId = null;

            if ($row->spotify_track_id) {
                $trackId = $this->trackCache['spotify:'.$row->spotify_track_id]
                    ?? DB::table('tracks')->where('spotify_track_id', $row->spotify_track_id)->value('id');
                if ($trackId) {
                    $this->trackCache['spotify:'.$row->spotify_track_id] = $trackId;
                }
            }

            // Fuzzy match by lowercase title
            if (! $trackId) {
                $trackId = DB::table('tracks')
                    ->whereRaw('LOWER(title) = ?', [mb_strtolower($row->track_name)])
                    ->value('id');
            }

            // No match — create new track + artist
            if (! $trackId) {
                $albumId = $row->album_name ? $this->findOrCreateAlbum($row->album_name, $row->album_image_url) : null;
                $trackId = $this->findOrCreateTrack(
                    title: $row->track_name,
                    spotifyTrackId: null,
                    albumId: $albumId,
                    durationMs: $row->duration_ms,
                    popularity: null,
                    previewUrl: null,
                    spotifyUri: null,
                );
                $this->syncTrackArtists($trackId, [$row->artist_name]);
            }

            $this->trackCache['lastfm:'.mb_strtolower($row->track_name).':'.mb_strtolower($row->artist_name)] = $trackId;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // Migrate plays
        $totalPlays = (clone $query)->count();
        $this->line("Migrating {$totalPlays} Last.fm plays...");

        $bar2 = $this->output->createProgressBar($totalPlays);
        $bar2->start();

        (clone $query)
            ->select('track_name', 'artist_name', 'spotify_track_id', 'played_at')
            ->orderBy('played_at')
            ->chunk(500, function ($rows) use ($bar2) {
                $inserts = [];
                $now = now();

                foreach ($rows as $row) {
                    $cacheKey = 'lastfm:'.mb_strtolower($row->track_name).':'.mb_strtolower($row->artist_name);
                    $trackId = $this->trackCache[$cacheKey] ?? null;

                    if (! $trackId && $row->spotify_track_id) {
                        $trackId = $this->trackCache['spotify:'.$row->spotify_track_id]
                            ?? DB::table('tracks')->where('spotify_track_id', $row->spotify_track_id)->value('id');
                    }

                    if (! $trackId) {
                        $trackId = DB::table('tracks')
                            ->whereRaw('LOWER(title) = ?', [mb_strtolower($row->track_name)])
                            ->value('id');
                    }

                    if (! $trackId) {
                        continue;
                    }

                    $inserts[] = [
                        'track_id' => $trackId,
                        'played_at' => $row->played_at,
                        'source' => 'lastfm',
                        'context' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($inserts) {
                    DB::table('plays')->insertOrIgnore($inserts);
                }

                $bar2->advance(count($rows));
            });

        $bar2->finish();
        $this->newLine();
        $this->info('Last.fm done.');
    }

    private function findOrCreateArtist(string $name): int
    {
        $key = mb_strtolower($name);

        if (isset($this->artistCache[$key])) {
            return $this->artistCache[$key];
        }

        $id = DB::table('artists')->whereRaw('LOWER(name) = ?', [$key])->value('id');

        if (! $id) {
            $id = DB::table('artists')->insertGetId([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $this->artistCache[$key] = $id;
    }

    private function findOrCreateAlbum(string $name, ?string $imageUrl): int
    {
        $key = mb_strtolower($name);

        if (isset($this->albumCache[$key])) {
            return $this->albumCache[$key];
        }

        $id = DB::table('albums')->whereRaw('LOWER(name) = ?', [$key])->value('id');

        if (! $id) {
            $id = DB::table('albums')->insertGetId([
                'name' => $name,
                'image_url' => $imageUrl,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $this->albumCache[$key] = $id;
    }

    private function findOrCreateTrack(
        string $title,
        ?string $spotifyTrackId,
        ?int $albumId,
        ?int $durationMs,
        ?int $popularity,
        ?string $previewUrl,
        ?string $spotifyUri,
    ): int {
        if ($spotifyTrackId) {
            $cacheKey = 'spotify:'.$spotifyTrackId;

            if (isset($this->trackCache[$cacheKey])) {
                return $this->trackCache[$cacheKey];
            }

            $id = DB::table('tracks')->where('spotify_track_id', $spotifyTrackId)->value('id');

            if (! $id) {
                $id = DB::table('tracks')->insertGetId([
                    'title' => $title,
                    'spotify_track_id' => $spotifyTrackId,
                    'album_id' => $albumId,
                    'duration_ms' => $durationMs,
                    'popularity' => $popularity,
                    'preview_url' => $previewUrl,
                    'spotify_uri' => $spotifyUri,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $this->trackCache[$cacheKey] = $id;
        }

        // No Spotify ID — match by lowercase title
        $id = DB::table('tracks')
            ->whereNull('spotify_track_id')
            ->whereRaw('LOWER(title) = ?', [mb_strtolower($title)])
            ->value('id');

        if (! $id) {
            $id = DB::table('tracks')->insertGetId([
                'title' => $title,
                'spotify_track_id' => null,
                'album_id' => $albumId,
                'duration_ms' => $durationMs,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $id;
    }

    private function syncTrackArtists(int $trackId, array $artistNames): void
    {
        $existing = DB::table('track_artists')->where('track_id', $trackId)->pluck('artist_id')->all();

        if (! empty($existing)) {
            return;
        }

        foreach ($artistNames as $index => $name) {
            $artistId = $this->findOrCreateArtist($name);

            DB::table('track_artists')->insertOrIgnore([
                'track_id' => $trackId,
                'artist_id' => $artistId,
                'is_primary' => $index === 0,
                'sort_order' => $index,
            ]);
        }
    }
}
