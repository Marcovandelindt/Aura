<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Services\Strava\StravaApiService;
use App\Services\Strava\StravaAuthService;
use App\Services\Strava\WeatherService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncStravaActivities extends Command
{
    protected $signature = 'strava:sync {--all : Sync all activities instead of only recent ones}';

    protected $description = 'Sync activities from Strava';

    public function handle(StravaAuthService $authService, StravaApiService $apiService, WeatherService $weatherService): int
    {
        if (! $authService->isConnected()) {
            $this->error('Strava is not connected. Visit /strava/auth to authenticate.');

            return self::FAILURE;
        }

        $this->info('Syncing Strava activities...');

        $page = 1;
        $synced = 0;
        $skipped = 0;

        do {
            $activities = $apiService->getActivities(page: $page, perPage: 100);

            if (empty($activities)) {
                break;
            }

            foreach ($activities as $data) {
                $existing = Activity::where('strava_id', $data['id'])->first();

                if ($existing && ! $this->option('all')) {
                    $skipped++;

                    continue;
                }

                $polyline = $data['map']['summary_polyline'] ?? null;

                $activityData = [
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'sport_type' => $data['sport_type'] ?? $data['type'],
                    'distance' => $data['distance'] ?? 0,
                    'moving_time' => $data['moving_time'] ?? 0,
                    'elapsed_time' => $data['elapsed_time'] ?? 0,
                    'total_elevation_gain' => $data['total_elevation_gain'] ?? 0,
                    'start_date' => $data['start_date'],
                    'start_date_local' => $data['start_date_local'],
                    'timezone' => $data['timezone'] ?? null,
                    'average_speed' => $data['average_speed'] ?? null,
                    'max_speed' => $data['max_speed'] ?? null,
                    'average_heartrate' => $data['average_heartrate'] ?? null,
                    'max_heartrate' => $data['max_heartrate'] ?? null,
                    'calories' => $data['kilojoules'] ?? null,
                    'description' => $data['description'] ?? null,
                    'map_polyline' => $polyline,
                ];

                // Fetch weather for new activities only
                if (! $existing && $polyline) {
                    $point = $weatherService->decodeFirstPoint($polyline);
                    if ($point) {
                        $weatherData = $weatherService->getForActivity(
                            $point['lat'],
                            $point['lng'],
                            Carbon::parse($data['start_date'])
                        );
                        if ($weatherData) {
                            $activityData = array_merge($activityData, $weatherData);
                        }
                    }
                }

                Activity::updateOrCreate(['strava_id' => $data['id']], $activityData);

                $synced++;
            }

            if ($skipped > 0) {
                $this->line("  Page {$page}: {$skipped} already synced, stopping.");
                break;
            }

            $this->line("  Page {$page}: synced ".count($activities).' activities');
            $page++;
        } while (count($activities) === 100);

        $this->newLine();
        $this->info("Done! Synced {$synced} activities.");

        return self::SUCCESS;
    }
}
