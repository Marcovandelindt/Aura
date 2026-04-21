<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportDatabase extends Command
{
    protected $signature = 'db:export {--output= : Path to export file (default: exports/YYYY-MM-DD.json)}';

    protected $description = 'Export all database tables to a JSON file';

    public function handle(): int
    {
        $output = $this->option('output') ?? 'exports/'.now()->format('Y-m-d').'.json';

        $directory = dirname(base_path($output));
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $tables = collect(DB::select('SHOW TABLES'))
            ->map(fn ($row) => array_values((array) $row)[0])
            ->filter(fn ($table) => ! in_array($table, ['migrations', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'sessions', 'telescope_entries', 'telescope_entries_tags', 'telescope_monitoring']))
            ->values();

        $export = [];

        foreach ($tables as $table) {
            $rows = DB::table($table)->get()->toArray();
            $export[$table] = array_map(fn ($row) => (array) $row, $rows);
            $this->line("  Exported <info>{$table}</info> (".count($rows).' rows)');
        }

        file_put_contents(base_path($output), json_encode($export, JSON_UNESCAPED_UNICODE));

        $this->newLine();
        $this->info("Database exported to: {$output}");

        return self::SUCCESS;
    }
}
