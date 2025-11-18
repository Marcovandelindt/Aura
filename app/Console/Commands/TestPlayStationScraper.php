<?php

namespace App\Console\Commands;

use App\Services\PlayStation\PlayStationScraperService;
use Illuminate\Console\Command;

class TestPlayStationScraper extends Command
{
    protected $signature = 'playstation:test {username} {--cookie=}';

    protected $description = 'Test PlayStation TimeTracker scraper connection';

    public function handle(PlayStationScraperService $scraper): int
    {
        $username = $this->argument('username');
        $cookie = $this->option('cookie');

        // First test public profile
        $this->info('Testing public profile...');
        $publicResult = $scraper->getPublicProfile($username);

        if ($publicResult['success']) {
            $this->info("✓ Public profile works! Found {$publicResult['games_count']} games");
            $this->table(
                ['Name', 'Platform', 'Hours', 'Sessions', 'Last Played'],
                $publicResult['games']
            );
        } else {
            $this->error('✗ Public profile failed: '.$publicResult['error']);
        }

        // Test authenticated playtimes if cookie provided
        if ($cookie) {
            $this->newLine();
            $this->info('Testing authenticated playtimes page...');

            $scraper->setSessionCookie($cookie);
            $authResult = $scraper->testConnection($username);

            if ($authResult['success']) {
                $this->info('✓ Session cookie is valid!');
                $this->info("Page title: {$authResult['title']}");
                $this->info("HTML length: {$authResult['html_length']} bytes");
                $this->newLine();
                $this->info('HTML Preview (first 2000 chars):');
                $this->line($authResult['html_preview']);
            } else {
                $this->error('✗ Auth failed: '.$authResult['error']);
            }
        } else {
            $this->warn('No cookie provided, skipping authenticated test');
        }

        return Command::SUCCESS;
    }
}
