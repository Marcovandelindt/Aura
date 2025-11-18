<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\PlayStation\PlayStationScraperService;
use Illuminate\Console\Command;

class SyncPlayStationSessions extends Command
{
    protected $signature = 'playstation:sync-sessions
                            {username?}
                            {--cookie= : Session cookie for authentication}
                            {--save-cookie : Save the cookie to settings}
                            {--all : Sync all pages, not just until existing data}';

    protected $description = 'Sync PlayStation game sessions from PS-Timetracker';

    public function handle(PlayStationScraperService $scraper): int
    {
        $username = $this->argument('username') ?? Setting::getPlayStationUsername() ?? 'Marchoofd';
        $cookie = $this->option('cookie') ?? Setting::getPlayStationSessionCookie();

        if (! $cookie) {
            $this->error('No session cookie provided.');
            $this->info('Use: php artisan playstation:sync-sessions --cookie="your_cookie_here"');

            return Command::FAILURE;
        }

        // Save cookie if requested
        if ($this->option('save-cookie') && $this->option('cookie')) {
            Setting::storePlayStationCredentials($username, $this->option('cookie'));
            $this->info('Cookie saved to settings.');
        }

        $this->info("Syncing sessions for: {$username}");

        $scraper->setSessionCookie($cookie);
        $result = $scraper->syncSessions($username);

        if ($result['success']) {
            $this->info("✓ {$result['message']}");
            if ($result['skipped'] > 0) {
                $this->warn("Skipped {$result['skipped']} sessions (game not found)");
            }

            return Command::SUCCESS;
        }

        $this->error('✗ Sync failed: '.($result['error'] ?? 'Unknown error'));

        return Command::FAILURE;
    }
}
