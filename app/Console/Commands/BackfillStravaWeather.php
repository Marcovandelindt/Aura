<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Services\Strava\WeatherService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BackfillStravaWeather extends Command
{
    protected $signature = 'strava:backfill-weather';

    protected $description = 'Backfill weather data for existing Strava activities';

    public function handle(WeatherService $weather): int
    {
        $activities = Activity::whereNull('weather_temp')
            ->whereNotNull('map_polyline')
            ->orderBy('start_date')
            ->get();

        if ($activities->isEmpty()) {
            $this->info('All activities already have weather data.');

            return self::SUCCESS;
        }

        $this->info("Fetching weather for {$activities->count()} activities...");
        $bar = $this->output->createProgressBar($activities->count());
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($activities as $activity) {
            $point = $weather->decodeFirstPoint($activity->map_polyline);

            if (! $point) {
                $failed++;
                $bar->advance();

                continue;
            }

            $date = Carbon::parse($activity->start_date);
            $data = $weather->getForActivity($point['lat'], $point['lng'], $date);

            if ($data) {
                $activity->update($data);
                $success++;
            } else {
                $failed++;
            }

            $bar->advance();

            // Respecteer Open-Meteo rate limits
            usleep(200000);
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done! {$success} updated, {$failed} failed.");

        return self::SUCCESS;
    }
}
