<?php

namespace App\Services\Spotify;

use Illuminate\Http\RedirectResponse;
use SpotifyWebAPI\Session as SpotifySession;
use SpotifyWebAPI\SpotifyWebAPI;

class SpotifyAuthService
{
    protected SpotifySession $spotifySession;

    protected SpotifyWebAPI $spotifyApi;

    public function __construct()
    {
        $this->spotifySession = new SpotifySession(
            config('spotify.client_id'),
            config('spotify.client_secret'),
            config('spotify.redirect_uri')
        );

        $this->spotifyApi = new SpotifyWebAPI;
    }

    /**
     * Redirect the user to Spotify for authentication
     */
    public function redirect(): RedirectResponse
    {
        $scopes = config('spotify.default_scopes');

        $authorizeUrl = $this->spotifySession->getAuthorizeUrl([
            'scope' => $scopes,
        ]);

        session(['spotify_auth_state' => $this->spotifySession->generateState()]);

        return redirect($authorizeUrl);
    }

    /**
     * Handle the callback from Spotify
     */
    public function handleCallback(string $code): array
    {
        $this->spotifySession->requestAccessToken($code);

        $accessToken = $this->spotifySession->getAccessToken();
        $refreshToken = $this->spotifySession->getRefreshToken();
        $expiresAt = time() + $this->spotifySession->getTokenExpiration();

        // Get user info from Spotify
        $this->spotifyApi->setAccessToken($accessToken);
        $spotifyUser = $this->spotifyApi->me();

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_at' => $expiresAt,
            'spotify_user' => $spotifyUser,
        ];
    }

    /**
     * Refresh the access token
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        $result = $this->spotifySession->refreshAccessToken($refreshToken);

        if (! $result || ! $this->spotifySession->getAccessToken()) {
            throw new \Exception('Failed to refresh Spotify access token.');
        }

        $newRefreshToken = $this->spotifySession->getRefreshToken();

        return [
            'access_token' => $this->spotifySession->getAccessToken(),
            'refresh_token' => $newRefreshToken ?: $refreshToken,
            'expires_at' => time() + $this->spotifySession->getTokenExpiration(),
        ];
    }

    /**
     * Check if the token is expired (or will expire within 5 minutes)
     */
    public function isTokenExpired(int $expiresAt): bool
    {
        return time() >= ($expiresAt - 300);
    }

    /**
     * Get authenticated Spotify API instance
     */
    public function getSpotifyApi(string $accessToken): SpotifyWebAPI
    {
        $api = new SpotifyWebAPI;
        $api->setAccessToken($accessToken);

        return $api;
    }
}
