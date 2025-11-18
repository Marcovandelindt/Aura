<?php

namespace App\Console\Commands;

use App\Services\PlayStation\PlayStationScraperService;
use Illuminate\Console\Command;

class SyncPlayStationGames extends Command
{
    protected $signature = 'playstation:sync {username?}';

    protected $description = 'Sync PlayStation games from PS-Timetracker';

    public function handle(PlayStationScraperService $scraper): int
    {
        $username = $this->argument('username') ?? config('services.playstation.username');

        if (! $username) {
            $this->error('No username provided. Pass as argument or set PLAYSTATION_USERNAME in .env');

            return Command::FAILURE;
        }

        $this->info("Syncing games for: {$username}");

        $result = $scraper->syncGames($username);

        if ($result['success']) {
            $this->info("✓ {$result['message']}");

            return Command::SUCCESS;
        }

        $this->error('✗ Sync failed: '.($result['error'] ?? 'Unknown error'));

        return Command::FAILURE;
    }
}
