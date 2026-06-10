<?php

namespace App\Services\Spotify;

use App\Models\Setting;
use SpotifyWebAPI\SpotifyWebAPI;

class SpotifyService
{
    protected SpotifyAuthService $authService;

    public function __construct(SpotifyAuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Get an authenticated Spotify API instance
     * Automatically refreshes token if expired
     */
    public function getAuthenticatedApi(): ?SpotifyWebAPI
    {
        $credentials = Setting::getSpotifyCredentials();

        if (! $credentials['access_token'] || ! $credentials['refresh_token']) {
            return null;
        }

        // Check if token is expired (or expiring within 5 minutes) and refresh if needed
        if ($this->authService->isTokenExpired($credentials['expires_at'])) {
            \Log::info('Spotify token expired, attempting refresh...');

            try {
                $newTokenData = $this->authService->refreshAccessToken($credentials['refresh_token']);

                Setting::set('spotify_access_token', $newTokenData['access_token']);
                Setting::set('spotify_token_expires_at', $newTokenData['expires_at']);
                Setting::set('spotify_refresh_token', $newTokenData['refresh_token']);

                $credentials['access_token'] = $newTokenData['access_token'];

                \Log::info('Spotify token refreshed successfully');
            } catch (\Exception $e) {
                \Log::error('Failed to refresh Spotify token: '.$e->getMessage());

                // Only force re-authentication when Spotify explicitly invalidates the
                // refresh token (invalid_grant). Transient errors (network, 5xx) should
                // not log the user out — they can retry on the next request.
                if (str_contains($e->getMessage(), 'invalid_grant')) {
                    Setting::remove('spotify_access_token');
                    Setting::remove('spotify_refresh_token');
                    Setting::remove('spotify_token_expires_at');
                }

                return null;
            }
        }

        return $this->authService->getSpotifyApi($credentials['access_token']);
    }

    /**
     * Check if Spotify is connected
     */
    public function isConnected(): bool
    {
        $credentials = Setting::getSpotifyCredentials();

        return ! empty($credentials['refresh_token']);
    }

    /**
     * Disconnect Spotify
     */
    public function disconnect(): void
    {
        Setting::remove('spotify_id');
        Setting::remove('spotify_access_token');
        Setting::remove('spotify_refresh_token');
        Setting::remove('spotify_token_expires_at');
    }
}
