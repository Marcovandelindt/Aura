<?php

namespace App\Console\Commands;

use App\Services\Spotify\SpotifyTrackService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncSpotifyTracks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spotify:sync-tracks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync recently played tracks from Spotify';

    protected SpotifyTrackService $trackService;

    public function __construct(SpotifyTrackService $trackService)
    {
        parent::__construct();
        $this->trackService = $trackService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting Spotify track sync...');
        
        try {
            $result = $this->trackService->syncRecentlyPlayed();
            
            $this->info("✓ Sync completed!");
            $this->info("  - Tracks synced: {$result['synced']}");
            $this->info("  - Duplicates skipped: {$result['skipped']}");
            $this->info("  - Total processed: {$result['total']}");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('✗ Sync failed: ' . $e->getMessage());
            Log::error('Spotify sync command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
}
