<?php

namespace App\Console\Commands;

use App\Models\Movie;
use App\Models\TvSeries;
use App\Services\PersonSyncService;
use App\Services\TMDB\TMDBMovieService;
use App\Services\TMDB\TMDBTVService;
use Illuminate\Console\Command;

class BackfillPeople extends Command
{
    protected $signature = 'backfill:people {--clear-cache : Clear TMDB cache before syncing}';

    protected $description = 'Backfill cast and crew for existing movies and TV series';

    public function handle(
        TMDBMovieService $movieService,
        TMDBTVService $tvService,
        PersonSyncService $personSyncService,
    ): int {
        $movies = Movie::all();
        $series = TvSeries::all();

        if ($this->option('clear-cache')) {
            $this->info('Clearing TMDB cache...');
            foreach ($series as $show) {
                $tvService->clearCache($show->tmdb_id);
            }
            foreach ($movies as $movie) {
                \Illuminate\Support\Facades\Cache::forget("tmdb_movie_details_{$movie->tmdb_id}");
            }
        }

        $total = $movies->count() + $series->count();

        if ($total === 0) {
            $this->info('Nothing to backfill.');

            return self::SUCCESS;
        }

        $this->info("Syncing people for {$movies->count()} movies and {$series->count()} TV series...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($movies as $movie) {
            $details = $movieService->getDetails($movie->tmdb_id);
            $personSyncService->syncForMovie($movie, $details['credits'] ?? []);
            $bar->advance();
            usleep(250000);
        }

        foreach ($series as $show) {
            $details = $tvService->getDetails($show->tmdb_id);
            $personSyncService->syncForTvSeries($show, $details['aggregate_credits'] ?? []);
            $bar->advance();
            usleep(250000);
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Done!');

        return self::SUCCESS;
    }
}
