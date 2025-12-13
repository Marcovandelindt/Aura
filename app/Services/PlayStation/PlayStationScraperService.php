<?php

namespace App\Services\PlayStation;

use App\Models\PlayStationGame;
use App\Models\PlayStationSession;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class PlayStationScraperService
{
    private const BASE_URL = 'https://ps-timetracker.com';

    public function __construct(
        private ?string $sessionCookie = null
    ) {}

    public function setSessionCookie(string $cookie): self
    {
        $this->sessionCookie = $cookie;

        return $this;
    }

    /**
     * Test if the session cookie is valid by fetching playtimes page
     */
    public function testConnection(string $username): array
    {
        $url = self::BASE_URL."/profile/{$username}/playtimes";

        $response = Http::withHeaders([
            'Cookie' => '_my_app_session='.$this->sessionCookie,
        ])->get($url);

        if (! $response->successful()) {
            return [
                'success' => false,
                'error' => 'HTTP Error: '.$response->status(),
            ];
        }

        $html = $response->body();

        // Check if we're redirected to login page
        if (str_contains($html, 'Your PSN-Name') && str_contains($html, 'Your Code')) {
            return [
                'success' => false,
                'error' => 'Session cookie is invalid or expired',
            ];
        }

        // Try to extract some data to confirm it works
        $crawler = new Crawler($html);

        // Return raw HTML snippet for analysis
        return [
            'success' => true,
            'html_length' => strlen($html),
            'title' => $crawler->filter('title')->count() ? $crawler->filter('title')->text() : 'No title',
            'html_preview' => substr($html, 0, 2000),
        ];
    }

    /**
     * Fetch and parse all games from public profile
     */
    public function scrapeGames(string $username): array
    {
        $url = self::BASE_URL."/profile/{$username}";

        $response = Http::get($url);

        if (! $response->successful()) {
            return [
                'success' => false,
                'error' => 'HTTP Error: '.$response->status(),
                'games' => [],
            ];
        }

        $crawler = new Crawler($response->body());

        $games = [];
        $crawler->filter('table tbody tr')->each(function (Crawler $row) use (&$games) {
            $cells = $row->filter('td');

            // Table structure: #, Image, Name, Platform, Hours, Sessions, Avg, Last Played, Trophies, %
            if ($cells->count() >= 8) {
                // Extract image URL from the image cell
                $imageUrl = null;
                $imageNode = $cells->eq(1)->filter('img');
                if ($imageNode->count()) {
                    $imageUrl = $imageNode->attr('src');
                }

                // Extract game URL from the name link
                $gameUrl = null;
                $linkNode = $cells->eq(2)->filter('a');
                if ($linkNode->count()) {
                    $gameUrl = $linkNode->attr('href');
                }

                // Parse hours (e.g., "586h" -> 586.0, "55m" -> 0.9)
                $hoursText = trim($cells->eq(4)->text());
                $hours = $this->parsePlaytime($hoursText);

                // Parse sessions
                $sessions = (int) preg_replace('/[^0-9]/', '', trim($cells->eq(5)->text()));

                // Parse average session (e.g., "41m" or "1h 30m")
                $avgText = trim($cells->eq(6)->text());
                $avgMinutes = $this->parseAvgSession($avgText);

                // Parse last played date
                $lastPlayedText = trim($cells->eq(7)->text());
                $lastPlayedAt = $this->parseLastPlayed($lastPlayedText);

                // Parse trophies and completion if available
                $trophies = null;
                $completion = null;
                if ($cells->count() >= 10) {
                    $trophies = (int) preg_replace('/[^0-9]/', '', trim($cells->eq(8)->text())) ?: null;
                    $completionText = trim($cells->eq(9)->text());
                    $completion = (float) preg_replace('/[^0-9.]/', '', $completionText) ?: null;
                }

                $games[] = [
                    'name' => trim($cells->eq(2)->text()),
                    'platform' => trim($cells->eq(3)->text()),
                    'image_url' => $imageUrl,
                    'hours' => $hours,
                    'sessions' => $sessions,
                    'avg_session_minutes' => $avgMinutes,
                    'last_played_at' => $lastPlayedAt,
                    'trophies' => $trophies,
                    'completion_percentage' => $completion,
                    'psn_url' => $gameUrl ? self::BASE_URL.$gameUrl : null,
                ];
            }
        });

        return [
            'success' => true,
            'games_count' => count($games),
            'games' => $games,
        ];
    }

    /**
     * Sync games from profile to database
     */
    public function syncGames(string $username): array
    {
        $result = $this->scrapeGames($username);

        if (! $result['success']) {
            return $result;
        }

        $games = $result['games'];

        if (empty($games)) {
            return [
                'success' => true,
                'synced' => 0,
                'message' => 'No games found to sync',
            ];
        }

        // Get games excluded from sync
        $excludedGames = PlayStationGame::where('exclude_from_sync', true)
            ->get()
            ->map(fn ($game) => $game->name.'|'.$game->platform)
            ->toArray();

        // Filter out excluded games
        $gamesToSync = array_filter($games, function ($game) use ($excludedGames) {
            return ! in_array($game['name'].'|'.$game['platform'], $excludedGames);
        });

        $skipped = count($games) - count($gamesToSync);

        if (empty($gamesToSync)) {
            return [
                'success' => true,
                'synced' => 0,
                'skipped' => $skipped,
                'message' => 'No games to sync (all excluded)',
            ];
        }

        // Upsert games that are not excluded
        PlayStationGame::upsert(
            array_values($gamesToSync),
            ['name', 'platform'], // Unique keys
            ['image_url', 'hours', 'sessions', 'avg_session_minutes', 'last_played_at', 'trophies', 'completion_percentage', 'psn_url'] // Update columns
        );

        return [
            'success' => true,
            'synced' => count($gamesToSync),
            'skipped' => $skipped,
            'message' => 'Successfully synced '.count($gamesToSync).' games'.($skipped > 0 ? " ({$skipped} excluded)" : ''),
        ];
    }

    /**
     * Parse playtime string to hours (e.g., "586h" -> 586.0, "55m" -> 0.9)
     */
    private function parsePlaytime(string $text): float
    {
        if (empty($text) || $text === '-') {
            return 0.0;
        }

        $hours = 0.0;

        // Match hours (e.g., "586h" or "586")
        if (preg_match('/(\d+)\s*h/i', $text, $matches)) {
            $hours += (float) $matches[1];
        }

        // Match minutes (e.g., "55m")
        if (preg_match('/(\d+)\s*m/i', $text, $matches)) {
            $hours += (float) $matches[1] / 60;
        }

        // If no unit found, check if it's just a number (assume hours for large numbers, minutes for small)
        if ($hours === 0.0 && preg_match('/^(\d+)$/', $text, $matches)) {
            $value = (float) $matches[1];
            // Assume it's hours if no unit specified
            $hours = $value;
        }

        return round($hours, 1);
    }

    /**
     * Parse average session time to minutes
     */
    private function parseAvgSession(string $text): ?int
    {
        if (empty($text) || $text === '-') {
            return null;
        }

        $minutes = 0;

        // Match hours (e.g., "1h")
        if (preg_match('/(\d+)h/', $text, $matches)) {
            $minutes += (int) $matches[1] * 60;
        }

        // Match minutes (e.g., "30m")
        if (preg_match('/(\d+)m/', $text, $matches)) {
            $minutes += (int) $matches[1];
        }

        return $minutes > 0 ? $minutes : null;
    }

    /**
     * Parse last played date string to Carbon date
     */
    private function parseLastPlayed(string $text): ?string
    {
        if (empty($text) || $text === '-') {
            return null;
        }

        try {
            // Handle relative dates like "Today", "Yesterday"
            if (stripos($text, 'today') !== false) {
                return Carbon::today()->toDateString();
            }
            if (stripos($text, 'yesterday') !== false) {
                return Carbon::yesterday()->toDateString();
            }

            // Try to parse as date
            return Carbon::parse($text)->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Fetch public profile data (no auth needed) - for testing
     */
    public function getPublicProfile(string $username): array
    {
        $result = $this->scrapeGames($username);

        if (! $result['success']) {
            return $result;
        }

        return [
            'success' => true,
            'games_count' => $result['games_count'],
            'games' => array_slice($result['games'], 0, 5), // First 5 for preview
        ];
    }

    /**
     * Sync sessions from playtimes page (requires authentication)
     */
    public function syncSessions(string $username): array
    {
        if (! $this->sessionCookie) {
            $this->sessionCookie = Setting::getPlayStationSessionCookie();
        }

        if (! $this->sessionCookie) {
            return [
                'success' => false,
                'error' => 'No session cookie configured',
            ];
        }

        // Build game lookup map (name+platform -> id)
        $gameMap = PlayStationGame::all()
            ->keyBy(fn ($game) => $game->name.'|'.$game->platform)
            ->map(fn ($game) => $game->id)
            ->toArray();

        if (empty($gameMap)) {
            return [
                'success' => false,
                'error' => 'No games found. Please sync games first.',
            ];
        }

        // Get the most recent session date to compare
        $latestSession = PlayStationSession::orderByDesc('started_at')->first();
        $latestSessionDate = $latestSession?->started_at;

        $totalSynced = 0;
        $totalSkipped = 0;
        $totalExisting = 0;
        $page = 1;
        $hasMorePages = true;

        while ($hasMorePages) {
            $result = $this->scrapeSessionsPage($username, $page);

            if (! $result['success']) {
                if ($page === 1) {
                    return $result;
                }
                break;
            }

            if (empty($result['sessions'])) {
                break;
            }

            $pageHasNewSessions = false;

            foreach ($result['sessions'] as $sessionData) {
                $gameKey = $sessionData['game_name'].'|'.$sessionData['platform'];
                $gameId = $gameMap[$gameKey] ?? null;

                if (! $gameId) {
                    $totalSkipped++;

                    continue;
                }

                // Parse the session start time
                $sessionStart = Carbon::parse($sessionData['started_at']);

                // Skip if this session is older than our latest stored session
                if ($latestSessionDate && $sessionStart->lte($latestSessionDate)) {
                    // Check if it exists (might be the exact same session)
                    $exists = PlayStationSession::where('play_station_game_id', $gameId)
                        ->where('started_at', $sessionData['started_at'])
                        ->exists();

                    if ($exists) {
                        $totalExisting++;

                        continue;
                    }
                }

                // Try to insert (might fail on duplicate)
                try {
                    PlayStationSession::create([
                        'play_station_game_id' => $gameId,
                        'started_at' => $sessionData['started_at'],
                        'ended_at' => $sessionData['ended_at'],
                        'duration_minutes' => $sessionData['duration_minutes'],
                    ]);

                    $totalSynced++;
                    $pageHasNewSessions = true;
                } catch (\Illuminate\Database\QueryException $e) {
                    // Duplicate entry, skip
                    $totalExisting++;
                }
            }

            // Stop if this entire page had no new sessions (all older than latest)
            if (! $pageHasNewSessions && $latestSessionDate) {
                break;
            }

            $hasMorePages = $result['has_more'];
            $page++;

            // Safety limit
            if ($page > 50) {
                break;
            }
        }

        return [
            'success' => true,
            'synced' => $totalSynced,
            'skipped' => $totalSkipped,
            'pages' => $page - 1,
            'message' => "Synced {$totalSynced} sessions from ".($page - 1).' pages',
        ];
    }

    /**
     * Scrape a single page of sessions
     */
    private function scrapeSessionsPage(string $username, int $page = 1): array
    {
        $url = self::BASE_URL."/profile/{$username}/playtimes?page={$page}";

        $response = Http::withHeaders([
            'Cookie' => '_my_app_session='.$this->sessionCookie,
        ])->get($url);

        if (! $response->successful()) {
            return [
                'success' => false,
                'error' => 'HTTP Error: '.$response->status(),
            ];
        }

        $html = $response->body();

        // Check if we're redirected to login page
        if (str_contains($html, 'Your PSN-Name') && str_contains($html, 'Your Code')) {
            return [
                'success' => false,
                'error' => 'Session cookie is invalid or expired',
            ];
        }

        $crawler = new Crawler($html);
        $sessions = [];

        // Parse session rows from table
        $crawler->filter('table tbody tr')->each(function (Crawler $row) use (&$sessions) {
            $cells = $row->filter('td');
            $cellCount = $cells->count();

            if ($cellCount >= 6) {
                // Column structure: Empty, Game, Platform, Duration, Start, End, Edit
                $gameName = trim($cells->eq(1)->text());
                $platform = trim($cells->eq(2)->text());
                $durationText = trim($cells->eq(3)->text());
                $startText = trim($cells->eq(4)->text());
                $endText = trim($cells->eq(5)->text());

                $startedAt = $this->parseSessionDateTime($startText);
                $endedAt = $this->parseSessionDateTime($endText);
                $durationMinutes = $this->parseSessionDuration($durationText);

                if ($gameName && $startedAt && $durationMinutes > 0) {
                    $sessions[] = [
                        'game_name' => $gameName,
                        'platform' => $platform,
                        'started_at' => $startedAt,
                        'ended_at' => $endedAt,
                        'duration_minutes' => $durationMinutes,
                    ];
                }
            }
        });

        // Check for next page
        $hasMore = $crawler->filter('a[rel="next"]')->count() > 0;

        return [
            'success' => true,
            'sessions' => $sessions,
            'has_more' => $hasMore,
            'page' => $page,
        ];
    }

    /**
     * Parse session datetime string
     */
    private function parseSessionDateTime(string $text): ?string
    {
        if (empty($text) || $text === '-') {
            return null;
        }

        try {
            return Carbon::parse($text)->toDateTimeString();
        } catch (\Exception $e) {
            Log::warning('Failed to parse session datetime: '.$text);

            return null;
        }
    }

    /**
     * Parse session duration to minutes
     * Handles: "17 minutes", "1:01 hours", "1 minute", "2h 30m"
     */
    private function parseSessionDuration(string $text): int
    {
        if (empty($text) || $text === '-') {
            return 0;
        }

        $minutes = 0;

        // Handle "X:XX hours" format (e.g., "1:01 hours")
        if (preg_match('/(\d+):(\d+)\s*hours?/i', $text, $matches)) {
            $minutes = (int) $matches[1] * 60 + (int) $matches[2];

            return $minutes;
        }

        // Handle "X hours" or "X hour"
        if (preg_match('/(\d+)\s*hours?/i', $text, $matches)) {
            $minutes += (int) $matches[1] * 60;
        }

        // Handle "X minutes" or "X minute"
        if (preg_match('/(\d+)\s*minutes?/i', $text, $matches)) {
            $minutes += (int) $matches[1];
        }

        // Handle shorthand "Xh" or "Xm"
        if (preg_match('/(\d+)\s*h\b/i', $text, $matches)) {
            $minutes += (int) $matches[1] * 60;
        }

        if (preg_match('/(\d+)\s*m\b/i', $text, $matches)) {
            $minutes += (int) $matches[1];
        }

        return $minutes;
    }
}
