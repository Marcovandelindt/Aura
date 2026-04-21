<?php

namespace App\Console\Commands;

use App\Models\TvSeries;
use App\Services\TMDB\TMDBTVService;
use Illuminate\Console\Command;

class BackfillTvSeriesEnglishName extends Command
{
    protected $signature = 'tv:backfill-english-name';

    protected $description = 'Backfill English name for TV series with a non-English original language';

    public function handle(TMDBTVService $tmdb): int
    {
        $series = TvSeries::whereNull('name_en')
            ->whereNotIn('original_language', ['en', 'nl'])
            ->get();

        if ($series->isEmpty()) {
            $this->info('Nothing to backfill.');

            return self::SUCCESS;
        }

        $this->info("Backfilling English names for {$series->count()} series...");
        $bar = $this->output->createProgressBar($series->count());
        $bar->start();

        foreach ($series as $show) {
            $nameEn = $tmdb->getEnglishName($show->tmdb_id);

            if ($nameEn && $nameEn !== $show->name) {
                $show->update(['name_en' => $nameEn]);
            }

            $bar->advance();
            usleep(250000);
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Done!');

        return self::SUCCESS;
    }
}
