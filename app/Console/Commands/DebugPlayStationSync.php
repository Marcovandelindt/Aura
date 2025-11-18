<?php

namespace App\Console\Commands;

use App\Models\PlayStationGame;
use App\Services\PlayStation\PlayStationScraperService;
use Illuminate\Console\Command;

class DebugPlayStationSync extends Command
{
    protected $signature = 'playstation:debug {username?}';

    protected $description = 'Debug PlayStation sync to see what data is being fetched';

    public function handle(PlayStationScraperService $scraper): int
    {
        $username = $this->argument('username') ?? config('services.playstation.username') ?? 'Marchoofd';

        $this->info("Fetching games for: {$username}");

        $result = $scraper->scrapeGames($username);

        if (! $result['success']) {
            $this->error('Failed: '.($result['error'] ?? 'Unknown error'));

            return Command::FAILURE;
        }

        $this->info("Found {$result['games_count']} games from PS-Timetracker");
        $this->newLine();

        // Show first 3 games from scraper
        $this->info('Sample scraped data (first 3 games):');
        foreach (array_slice($result['games'], 0, 3) as $game) {
            $this->line("  - {$game['name']} ({$game['platform']})");
            $this->line("    Hours: {$game['hours']}, Sessions: {$game['sessions']}");
            $this->line("    Last played: {$game['last_played_at']}");
        }

        $this->newLine();

        // Compare with database
        $this->info('Comparing with database...');
        $dbGames = PlayStationGame::orderByDesc('last_played_at')->limit(3)->get();

        $this->info('Database data (3 most recent):');
        foreach ($dbGames as $game) {
            $this->line("  - {$game->name} ({$game->platform})");
            $this->line("    Hours: {$game->hours}, Sessions: {$game->sessions}");
            $this->line("    Last played: {$game->last_played_at}");
            $this->line("    Updated at: {$game->updated_at}");
        }

        $this->newLine();

        // Check for differences
        $this->info('Checking for updates needed...');
        $needsUpdate = 0;

        foreach ($result['games'] as $scrapedGame) {
            $dbGame = PlayStationGame::where('name', $scrapedGame['name'])
                ->where('platform', $scrapedGame['platform'])
                ->first();

            if ($dbGame) {
                $changed = false;
                $changes = [];

                if ($dbGame->hours != $scrapedGame['hours']) {
                    $changes[] = "hours: {$dbGame->hours} -> {$scrapedGame['hours']}";
                    $changed = true;
                }

                if ($dbGame->sessions != $scrapedGame['sessions']) {
                    $changes[] = "sessions: {$dbGame->sessions} -> {$scrapedGame['sessions']}";
                    $changed = true;
                }

                if ($dbGame->last_played_at != $scrapedGame['last_played_at']) {
                    $changes[] = "last_played: {$dbGame->last_played_at} -> {$scrapedGame['last_played_at']}";
                    $changed = true;
                }

                if ($changed) {
                    $needsUpdate++;
                    $this->warn("  {$scrapedGame['name']} needs update:");
                    foreach ($changes as $change) {
                        $this->line("    - {$change}");
                    }
                }
            }
        }

        if ($needsUpdate > 0) {
            $this->newLine();
            $this->warn("Found {$needsUpdate} games that need updating!");
        } else {
            $this->newLine();
            $this->info('All games are up to date!');
        }

        return Command::SUCCESS;
    }
}
