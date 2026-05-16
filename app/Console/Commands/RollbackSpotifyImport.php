<?php

namespace App\Console\Commands;

use App\Models\Play;
use Illuminate\Console\Command;

class RollbackSpotifyImport extends Command
{
    protected $signature = 'spotify:rollback-import';

    protected $description = 'Delete all plays that were added via the Spotify history import';

    public function handle(): int
    {
        $count = Play::where('from_import', true)->count();

        if ($count === 0) {
            $this->info('No imported plays found. Nothing to roll back.');

            return Command::SUCCESS;
        }

        $this->warn("This will permanently delete {$count} imported plays.");
        $this->line('Plays synced via the regular Spotify sync will not be affected.');

        if (! $this->confirm('Are you sure?', false)) {
            $this->line('Aborted.');

            return Command::SUCCESS;
        }

        Play::where('from_import', true)->delete();

        $this->info("{$count} imported plays deleted.");

        return Command::SUCCESS;
    }
}
