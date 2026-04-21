<?php

namespace App\Services\Strava;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    private const BASE_URL = 'https://archive-api.open-meteo.com/v1/archive';

    private const WMO_CODES = [
        0 => 'Clear sky',
        1 => 'Mainly clear', 2 => 'Partly cloudy', 3 => 'Overcast',
        45 => 'Fog', 48 => 'Icy fog',
        51 => 'Light drizzle', 53 => 'Drizzle', 55 => 'Heavy drizzle',
        61 => 'Light rain', 63 => 'Rain', 65 => 'Heavy rain',
        71 => 'Light snow', 73 => 'Snow', 75 => 'Heavy snow',
        80 => 'Light showers', 81 => 'Showers', 82 => 'Heavy showers',
        95 => 'Thunderstorm',
    ];

    /** @return array<string, mixed>|null */
    public function getForActivity(float $lat, float $lng, Carbon $date): ?array
    {
        $dateStr = $date->format('Y-m-d');
        $hour = $date->hour;

        $response = Http::withoutVerifying()
            ->get(self::BASE_URL, [
                'latitude' => $lat,
                'longitude' => $lng,
                'start_date' => $dateStr,
                'end_date' => $dateStr,
                'hourly' => 'temperature_2m,apparent_temperature,precipitation,wind_speed_10m,weather_code',
                'timezone' => 'UTC',
            ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();
        $hourly = $data['hourly'] ?? [];

        if (empty($hourly['temperature_2m'])) {
            return null;
        }

        $code = $hourly['weather_code'][$hour] ?? 0;

        return [
            'weather_temp' => round($hourly['temperature_2m'][$hour] ?? 0, 1),
            'weather_feels_like' => round($hourly['apparent_temperature'][$hour] ?? 0, 1),
            'weather_precipitation' => round($hourly['precipitation'][$hour] ?? 0, 1),
            'weather_wind_speed' => round($hourly['wind_speed_10m'][$hour] ?? 0, 1),
            'weather_condition' => self::WMO_CODES[$code] ?? 'Unknown',
            'weather_condition_code' => $code,
        ];
    }

    /** Decode first coordinate from an encoded polyline */
    public function decodeFirstPoint(string $polyline): ?array
    {
        if (empty($polyline)) {
            return null;
        }

        $index = 0;
        $lat = 0;
        $lng = 0;

        // Decode latitude
        $shift = 0;
        $result = 0;
        do {
            if ($index >= strlen($polyline)) {
                return null;
            }
            $b = ord($polyline[$index++]) - 63;
            $result |= ($b & 0x1F) << $shift;
            $shift += 5;
        } while ($b >= 0x20);
        $lat += ($result & 1) ? ~($result >> 1) : ($result >> 1);

        // Decode longitude
        $shift = 0;
        $result = 0;
        do {
            if ($index >= strlen($polyline)) {
                return null;
            }
            $b = ord($polyline[$index++]) - 63;
            $result |= ($b & 0x1F) << $shift;
            $shift += 5;
        } while ($b >= 0x20);
        $lng += ($result & 1) ? ~($result >> 1) : ($result >> 1);

        return ['lat' => $lat / 1e5, 'lng' => $lng / 1e5];
    }

    public static function conditionIcon(int $code): string
    {
        return match (true) {
            $code === 0 => 'fa-sun',
            $code <= 2 => 'fa-cloud-sun',
            $code === 3 => 'fa-cloud',
            $code <= 48 => 'fa-smog',
            $code <= 67 => 'fa-cloud-rain',
            $code <= 77 => 'fa-snowflake',
            $code <= 82 => 'fa-cloud-showers-heavy',
            default => 'fa-bolt',
        };
    }
}
