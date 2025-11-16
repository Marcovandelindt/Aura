<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Spotify API Credentials
    |--------------------------------------------------------------------------
    |
    | Here you may specify your Spotify API credentials. You can create
    | a new app at https://developer.spotify.com/dashboard/applications
    |
    */

    'client_id' => env('SPOTIFY_CLIENT_ID'),
    'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
    'redirect_uri' => env('SPOTIFY_REDIRECT_URI', env('APP_URL') . '/spotify/callback'),

    /*
    |--------------------------------------------------------------------------
    | Default Scopes
    |--------------------------------------------------------------------------
    |
    | These are the default scopes that will be requested when authenticating
    | with Spotify. You can override these on a per-request basis.
    |
    */

    'default_scopes' => [
        'user-read-email',
        'user-read-private',
        'user-read-playback-state',
        'user-modify-playback-state',
        'user-read-currently-playing',
        'user-read-recently-played',
        'user-top-read',
        'user-library-read',
        'playlist-read-private',
        'playlist-read-collaborative',
    ],

];