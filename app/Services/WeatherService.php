<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    private const CURRENT_URL = 'https://api.openweathermap.org/data/2.5/weather';

    private const FORECAST_URL = 'https://api.openweathermap.org/data/2.5/forecast';

    private const CACHE_KEY = 'weather.data';

    private const CACHE_TTL = 600; // 10 minutes

    public function get(): ?array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, fn () => $this->fetch());
    }

    private function fetch(): ?array
    {
        $params = [
            'lat' => config('weather.latitude'),
            'lon' => config('weather.longitude'),
            'appid' => config('weather.api_key'),
            'units' => 'metric',
            'lang' => 'nl',
        ];

        $currentResponse = Http::timeout(5)->get(self::CURRENT_URL, $params);
        $forecastResponse = Http::timeout(5)->get(self::FORECAST_URL, $params);

        if (! $currentResponse->successful() || ! $forecastResponse->successful()) {
            return null;
        }

        return $this->format($currentResponse->json(), $forecastResponse->json());
    }

    private function format(array $current, array $forecast): array
    {
        $tz = config('weather.timezone');
        $now = Carbon::now($tz);
        $todayStr = $now->toDateString();

        $weatherIcon = $current['weather'][0]['icon'] ?? '01d';
        $isDay = str_ends_with($weatherIcon, 'd');
        $windSpeedKmh = round(($current['wind']['speed'] ?? 0) * 3.6);

        // Hourly: 3-hourly slots for the rest of today
        $nextHours = [];
        foreach ($forecast['list'] as $slot) {
            $slotTime = Carbon::createFromTimestamp($slot['dt'], $tz);
            if ($slotTime->toDateString() !== $todayStr) {
                break;
            }
            if ($slotTime <= $now) {
                continue;
            }
            $precipMm = ($slot['rain']['3h'] ?? 0) + ($slot['snow']['3h'] ?? 0);
            $nextHours[] = [
                'time' => $slotTime->format('H:i'),
                'temp' => round($slot['main']['temp']),
                'icon' => $this->iconToFa($slot['weather'][0]['icon'] ?? '01d'),
                'precip_prob' => round(($slot['pop'] ?? 0) * 100),
                'precip' => round($precipMm, 1),
            ];
        }

        // Today's high/low + precip: combine current reading with today's forecast slots
        $todayTemps = [$current['main']['temp']];
        $todayPop = 0;
        $todayPrecipSum = ($current['rain']['1h'] ?? 0);
        foreach ($forecast['list'] as $slot) {
            $slotTime = Carbon::createFromTimestamp($slot['dt'], $tz);
            if ($slotTime->toDateString() !== $todayStr) {
                continue;
            }
            $todayTemps[] = $slot['main']['temp_min'];
            $todayTemps[] = $slot['main']['temp_max'];
            $todayPop = max($todayPop, $slot['pop'] ?? 0);
            $todayPrecipSum += ($slot['rain']['3h'] ?? 0) + ($slot['snow']['3h'] ?? 0);
        }

        // Daily: group forecast slots by date, skip today, take up to 6 days
        $dailyMap = [];
        foreach ($forecast['list'] as $slot) {
            $slotTime = Carbon::createFromTimestamp($slot['dt'], $tz);
            $dateStr = $slotTime->toDateString();
            if ($dateStr === $todayStr) {
                continue;
            }
            if (! isset($dailyMap[$dateStr])) {
                $dailyMap[$dateStr] = ['date' => $slotTime->copy()->startOfDay(), 'temps' => [], 'pop' => 0, 'icons' => []];
            }
            $dailyMap[$dateStr]['temps'][] = $slot['main']['temp_min'];
            $dailyMap[$dateStr]['temps'][] = $slot['main']['temp_max'];
            $dailyMap[$dateStr]['pop'] = max($dailyMap[$dateStr]['pop'], $slot['pop'] ?? 0);
            $dailyMap[$dateStr]['icons'][] = $slot['weather'][0]['icon'] ?? '01d';
        }

        $days = [];
        foreach (array_slice($dailyMap, 0, 6) as $data) {
            $icon = $this->pickRepresentativeIcon($data['icons']);
            $days[] = [
                'date' => $data['date'],
                'max' => round(max($data['temps'])),
                'min' => round(min($data['temps'])),
                'icon' => $this->iconToFa($icon),
                'condition' => $this->iconToCondition($icon),
                'precip_prob' => round($data['pop'] * 100),
            ];
        }

        return [
            'location' => config('weather.location_name'),
            'updated_at' => $now->format('H:i'),
            'current' => [
                'temp' => round($current['main']['temp']),
                'feels_like' => round($current['main']['feels_like']),
                'humidity' => $current['main']['humidity'],
                'wind_speed' => $windSpeedKmh,
                'wind_direction' => $this->degreesToCompass($current['wind']['deg'] ?? 0),
                'precipitation' => $current['rain']['1h'] ?? 0,
                'is_day' => $isDay,
                'condition' => $this->iconToCondition($weatherIcon),
                'icon' => $this->iconToFa($weatherIcon),
            ],
            'today' => [
                'max' => round(max($todayTemps)),
                'min' => round(min($todayTemps)),
                'precip_prob' => round($todayPop * 100),
                'precip_sum' => round($todayPrecipSum, 1),
                'sunrise' => Carbon::createFromTimestamp($current['sys']['sunrise'], $tz)->format('H:i'),
                'sunset' => Carbon::createFromTimestamp($current['sys']['sunset'], $tz)->format('H:i'),
            ],
            'hourly' => $nextHours,
            'daily' => $days,
        ];
    }

    private function pickRepresentativeIcon(array $icons): string
    {
        // Worst → best conditions, prefer daytime icons
        $priority = ['11d', '13d', '09d', '10d', '50d', '04d', '03d', '02d', '01d', '11n', '13n', '09n', '10n', '50n', '04n', '03n', '02n', '01n'];
        foreach ($priority as $candidate) {
            if (in_array($candidate, $icons, true)) {
                return $candidate;
            }
        }

        return $icons[0] ?? '01d';
    }

    private function iconToFa(string $icon): string
    {
        $code = substr($icon, 0, -1);
        $isDay = str_ends_with($icon, 'd');

        return match ($code) {
            '01' => $isDay ? 'fa-sun' : 'fa-moon',
            '02' => $isDay ? 'fa-cloud-sun' : 'fa-cloud-moon',
            '03', '04' => 'fa-cloud',
            '09' => 'fa-cloud-rain',
            '10' => $isDay ? 'fa-cloud-sun-rain' : 'fa-cloud-moon-rain',
            '11' => 'fa-cloud-bolt',
            '13' => 'fa-snowflake',
            '50' => 'fa-smog',
            default => 'fa-cloud',
        };
    }

    private function iconToCondition(string $icon): string
    {
        $code = substr($icon, 0, -1);

        return match ($code) {
            '01' => 'Helder',
            '02' => 'Licht bewolkt',
            '03' => 'Bewolkt',
            '04' => 'Zwaar bewolkt',
            '09' => 'Buien',
            '10' => 'Regen',
            '11' => 'Onweer',
            '13' => 'Sneeuw',
            '50' => 'Mist',
            default => 'Onbekend',
        };
    }

    private function degreesToCompass(float $degrees): string
    {
        $directions = ['N', 'NO', 'O', 'ZO', 'Z', 'ZW', 'W', 'NW'];

        return $directions[(int) round($degrees / 45) % 8];
    }
}
