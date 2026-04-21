<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportDatabase extends Command
{
    protected $signature = 'db:import {file : Path to the JSON export file}';

    protected $description = 'Import database from a JSON export file';

    public function handle(): int
    {
        $file = base_path($this->argument('file'));

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        $export = json_decode(file_get_contents($file), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON file.');

            return self::FAILURE;
        }

        if (! $this->confirm('This will truncate and overwrite existing data. Continue?')) {
            return self::SUCCESS;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($export as $table => $rows) {
            DB::table($table)->truncate();

            $count = count($rows);

            if ($count > 0) {
                $bar = $this->output->createProgressBar($count);
                $bar->setFormat("  {$table}: %current%/%max% [%bar%] %percent:3s%%");
                $bar->start();

                foreach (array_chunk($rows, 1000) as $chunk) {
                    DB::table($table)->insert($chunk);
                    $bar->advance(count($chunk));
                }

                $bar->finish();
                $this->newLine();
            } else {
                $this->line("  <info>{$table}</info>: 0 rows");
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->newLine();
        $this->info('Database imported successfully.');

        return self::SUCCESS;
    }
}
