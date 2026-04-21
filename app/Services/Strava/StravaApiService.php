<?php

namespace App\Services\Strava;

use Illuminate\Support\Facades\Http;

class StravaApiService
{
    private const BASE_URL = 'https://www.strava.com/api/v3';

    public function __construct(private readonly StravaAuthService $authService) {}

    /** @return array<int, array<string, mixed>> */
    public function getActivities(int $page = 1, int $perPage = 100): array
    {
        $token = $this->authService->getValidAccessToken();

        return Http::withoutVerifying()
            ->withToken($token)
            ->get(self::BASE_URL.'/athlete/activities', [
                'page' => $page,
                'per_page' => $perPage,
            ])
            ->throw()
            ->json();
    }
}
