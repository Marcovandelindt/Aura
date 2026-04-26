<?php

namespace App\Services\Lastfm;

use Illuminate\Support\Facades\Http;

class LastfmService
{
    private const BASE_URL = 'https://ws.audioscrobbler.com/2.0/';

    private const PAGE_LIMIT = 200;

    public function __construct(
        private readonly string $apiKey = '',
        private readonly string $username = '',
    ) {}

    public static function make(): self
    {
        return new self(
            apiKey: config('services.lastfm.api_key', ''),
            username: config('services.lastfm.username', ''),
        );
    }

    public static function fromConfig(): self
    {
        return self::make();
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiKey) && ! empty($this->username);
    }

    /**
     * Get total scrobble count for the user.
     */
    public function getTotalScrobbles(): int
    {
        $response = $this->get('user.getinfo', ['user' => $this->username]);

        return (int) ($response['user']['playcount'] ?? 0);
    }

    /**
     * Get a single page of recent tracks.
     *
     * @return array{tracks: array, totalPages: int, total: int}
     */
    public function getRecentTracksPage(int $page = 1, ?int $from = null, ?int $to = null): array
    {
        $params = [
            'user' => $this->username,
            'limit' => self::PAGE_LIMIT,
            'page' => $page,
            'extended' => 0,
        ];

        if ($from) {
            $params['from'] = $from;
        }

        if ($to) {
            $params['to'] = $to;
        }

        $response = $this->get('user.getrecenttracks', $params);

        $recentTracks = $response['recenttracks'] ?? [];
        $attr = $recentTracks['@attr'] ?? [];

        return [
            'tracks' => $recentTracks['track'] ?? [],
            'totalPages' => (int) ($attr['totalPages'] ?? 1),
            'total' => (int) ($attr['total'] ?? 0),
        ];
    }

    private function get(string $method, array $params = []): array
    {
        $response = Http::get(self::BASE_URL, array_merge($params, [
            'method' => $method,
            'api_key' => $this->apiKey,
            'format' => 'json',
        ]));

        $response->throw();

        return $response->json();
    }
}
