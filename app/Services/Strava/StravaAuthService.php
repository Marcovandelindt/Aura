<?php

namespace App\Services\Strava;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;

class StravaAuthService
{
    private const AUTH_URL = 'https://www.strava.com/oauth/authorize';

    private const TOKEN_URL = 'https://www.strava.com/oauth/token';

    public function redirect(): RedirectResponse
    {
        $query = http_build_query([
            'client_id' => config('strava.client_id'),
            'redirect_uri' => config('strava.redirect_uri'),
            'response_type' => 'code',
            'scope' => 'read,activity:read_all',
            'approval_prompt' => 'auto',
        ]);

        return redirect(self::AUTH_URL.'?'.$query);
    }

    public function handleCallback(string $code): array
    {
        $response = Http::withoutVerifying()->post(self::TOKEN_URL, [
            'client_id' => config('strava.client_id'),
            'client_secret' => config('strava.client_secret'),
            'code' => $code,
            'grant_type' => 'authorization_code',
        ])->throw()->json();

        return [
            'access_token' => $response['access_token'],
            'refresh_token' => $response['refresh_token'],
            'expires_at' => $response['expires_at'],
            'athlete' => $response['athlete'],
        ];
    }

    public function refreshAccessToken(): string
    {
        $refreshToken = Setting::get('strava_refresh_token');

        $response = Http::withoutVerifying()->post(self::TOKEN_URL, [
            'client_id' => config('strava.client_id'),
            'client_secret' => config('strava.client_secret'),
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ])->throw()->json();

        Setting::set('strava_access_token', $response['access_token']);
        Setting::set('strava_refresh_token', $response['refresh_token']);
        Setting::set('strava_token_expires_at', $response['expires_at']);

        return $response['access_token'];
    }

    public function getValidAccessToken(): string
    {
        $expiresAt = Setting::get('strava_token_expires_at');

        if (time() >= $expiresAt) {
            return $this->refreshAccessToken();
        }

        return Setting::get('strava_access_token');
    }

    public function isConnected(): bool
    {
        return (bool) Setting::get('strava_access_token');
    }
}
