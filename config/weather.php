<?php

return [
    'api_key' => env('OPEN_WEATHERMAP_API_KEY'),
    'latitude' => env('WEATHER_LATITUDE', 52.3676),
    'longitude' => env('WEATHER_LONGITUDE', 4.9041),
    'location_name' => env('WEATHER_LOCATION_NAME', 'Amsterdam'),
    'timezone' => env('WEATHER_TIMEZONE', 'Europe/Amsterdam'),
];
