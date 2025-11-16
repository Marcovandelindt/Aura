<?php

namespace App\Http\Controllers\Spotify;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Spotify\SpotifyAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SpotifyAuthController extends Controller
{
    protected SpotifyAuthService $spotifyAuthService;

    public function __construct(SpotifyAuthService $spotifyAuthService)
    {
        $this->spotifyAuthService = $spotifyAuthService;
    }

    /**
     * Redirect to Spotify for authentication
     */
    public function redirect()
    {
        return $this->spotifyAuthService->redirect();
    }

    /**
     * Handle the callback from Spotify
     */
    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('home.index')->with('error', 'Spotify authentication was cancelled.');
        }

        try {
            $tokenData = $this->spotifyAuthService->handleCallback($request->get('code'));
            
            // Store the tokens in settings
            Setting::storeSpotifyCredentials([
                'spotify_id' => $tokenData['spotify_user']->id,
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'],
                'expires_at' => $tokenData['expires_at'],
            ]);
            
            return redirect()->route('home.index')->with('success', 'Successfully connected to Spotify!');
            
        } catch (\Exception $e) {
            Log::error('Spotify authentication error: ' . $e->getMessage());
            return redirect()->route('home.index')->with('error', 'Failed to authenticate with Spotify.');
        }
    }
}