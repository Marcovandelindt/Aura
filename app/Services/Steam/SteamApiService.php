<?php

namespace App\Services\Steam;

use App\Models\SteamGame;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class SteamApiService
{
    private const API_BASE = 'https://api.steampowered.com';

    private ?string $apiKey;

    private ?string $steamId;

    public function __construct()
    {
        $this->apiKey = config('services.steam.api_key');
        $this->steamId = config('services.steam.steam_id');
    }

    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function setSteamId(string $steamId): self
    {
        $this->steamId = $steamId;

        return $this;
    }

    /**
     * Get owned games with playtime
     */
    public function getOwnedGames(): array
    {
        if (! $this->apiKey || ! $this->steamId) {
            return [
                'success' => false,
                'error' => 'Steam API key or Steam ID not configured',
            ];
        }

        $response = Http::get(self::API_BASE.'/IPlayerService/GetOwnedGames/v1/', [
            'key' => $this->apiKey,
            'steamid' => $this->steamId,
            'include_appinfo' => 1,
            'include_played_free_games' => 1,
            'format' => 'json',
        ]);

        if (! $response->successful()) {
            return [
                'success' => false,
                'error' => 'HTTP Error: '.$response->status(),
            ];
        }

        $data = $response->json();

        if (! isset($data['response']['games'])) {
            return [
                'success' => false,
                'error' => 'Invalid response from Steam API. Make sure your profile is public.',
            ];
        }

        $games = collect($data['response']['games'])->map(function ($game) {
            return [
                'steam_appid' => $game['appid'],
                'name' => $game['name'] ?? 'Unknown Game',
                'image_url' => isset($game['img_icon_url'])
                    ? "https://media.steampowered.com/steamcommunity/public/images/apps/{$game['appid']}/{$game['img_icon_url']}.jpg"
                    : null,
                'playtime_minutes' => $game['playtime_forever'] ?? 0,
                'playtime_2weeks_minutes' => $game['playtime_2weeks'] ?? null,
                'last_played_at' => isset($game['rtime_last_played']) && $game['rtime_last_played'] > 0
                    ? Carbon::createFromTimestamp($game['rtime_last_played'])
                    : null,
            ];
        })->toArray();

        return [
            'success' => true,
            'game_count' => $data['response']['game_count'] ?? count($games),
            'games' => $games,
        ];
    }

    /**
     * Get recently played games (last 2 weeks)
     */
    public function getRecentlyPlayedGames(): array
    {
        if (! $this->apiKey || ! $this->steamId) {
            return [
                'success' => false,
                'error' => 'Steam API key or Steam ID not configured',
            ];
        }

        $response = Http::get(self::API_BASE.'/IPlayerService/GetRecentlyPlayedGames/v1/', [
            'key' => $this->apiKey,
            'steamid' => $this->steamId,
            'format' => 'json',
        ]);

        if (! $response->successful()) {
            return [
                'success' => false,
                'error' => 'HTTP Error: '.$response->status(),
            ];
        }

        $data = $response->json();

        return [
            'success' => true,
            'total_count' => $data['response']['total_count'] ?? 0,
            'games' => $data['response']['games'] ?? [],
        ];
    }

    /**
     * Sync all games to database
     */
    public function syncGames(): array
    {
        $result = $this->getOwnedGames();

        if (! $result['success']) {
            return $result;
        }

        $games = $result['games'];

        if (empty($games)) {
            return [
                'success' => true,
                'synced' => 0,
                'message' => 'No games found',
            ];
        }

        // Upsert all games
        foreach ($games as $gameData) {
            SteamGame::updateOrCreate(
                ['steam_appid' => $gameData['steam_appid']],
                [
                    'name' => $gameData['name'],
                    'image_url' => $gameData['image_url'],
                    'playtime_minutes' => $gameData['playtime_minutes'],
                    'playtime_2weeks_minutes' => $gameData['playtime_2weeks_minutes'],
                    'last_played_at' => $gameData['last_played_at'],
                ]
            );
        }

        return [
            'success' => true,
            'synced' => count($games),
            'message' => 'Successfully synced '.count($games).' games',
        ];
    }

    /**
     * Test API connection
     */
    public function testConnection(): array
    {
        if (! $this->apiKey) {
            return [
                'success' => false,
                'error' => 'Steam API key not configured',
            ];
        }

        if (! $this->steamId) {
            return [
                'success' => false,
                'error' => 'Steam ID not configured',
            ];
        }

        $result = $this->getOwnedGames();

        if ($result['success']) {
            return [
                'success' => true,
                'message' => "Connection successful. Found {$result['game_count']} games.",
            ];
        }

        return $result;
    }

    /**
     * Resolve vanity URL to Steam ID
     */
    public function resolveVanityUrl(string $vanityUrl): array
    {
        if (! $this->apiKey) {
            return [
                'success' => false,
                'error' => 'Steam API key not configured',
            ];
        }

        $response = Http::get(self::API_BASE.'/ISteamUser/ResolveVanityURL/v1/', [
            'key' => $this->apiKey,
            'vanityurl' => $vanityUrl,
        ]);

        if (! $response->successful()) {
            return [
                'success' => false,
                'error' => 'HTTP Error: '.$response->status(),
            ];
        }

        $data = $response->json();

        if (($data['response']['success'] ?? 0) !== 1) {
            return [
                'success' => false,
                'error' => 'Could not resolve vanity URL',
            ];
        }

        return [
            'success' => true,
            'steam_id' => $data['response']['steamid'],
        ];
    }
}
