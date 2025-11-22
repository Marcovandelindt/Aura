<?php

return [

    /*
    |--------------------------------------------------------------------------
    | TMDB API Token
    |--------------------------------------------------------------------------
    | This should be your API Read Access Token (v4 auth)
    | You can access this token from your TMDB account settings (under API).
    */
    'token' => env('TMDB_API_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | TMDB API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for The Movie Database (TMDB) API integration.
    | You can get an API token from: https://www.themoviedb.org/settings/api
    |
    */

    'base_url' => 'https://api.themoviedb.org/3',

    'image_base_url' => 'https://image.tmdb.org/t/p/',

    /*
    |--------------------------------------------------------------------------
    | Image Sizes
    |--------------------------------------------------------------------------
    |
    | Available image sizes from TMDB.
    | See: https://developer.themoviedb.org/docs/image-basics
    |
    */

    'poster_sizes' => [
        'small' => 'w185',
        'medium' => 'w342',
        'large' => 'w500',
        'original' => 'original',
    ],

    'backdrop_sizes' => [
        'small' => 'w300',
        'medium' => 'w780',
        'large' => 'w1280',
        'original' => 'original',
    ],

    'profile_sizes' => [
        'small' => 'w45',
        'medium' => 'w185',
        'large' => 'h632',
        'original' => 'original',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Cache duration in minutes for TMDB API responses
    |
    */

    'cache_duration' => 60 * 24, // 24 hours

    /*
    |--------------------------------------------------------------------------
    | Language
    |--------------------------------------------------------------------------
    |
    | Default language for TMDB API requests
    |
    */

    'language' => 'nl-NL',

    /*
    |--------------------------------------------------------------------------
    | Region
    |--------------------------------------------------------------------------
    |
    | Default region for TMDB API requests (affects release dates, etc.)
    |
    */

    'region' => 'NL',

];
