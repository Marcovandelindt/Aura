<?php

namespace App\Http\Controllers;

use App\Enums\BacklogStatus;
use App\Models\NintendoSwitchGame;
use App\Models\PlayStationGame;
use App\Models\SteamGame;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RecommendationController extends Controller
{
    public function recommend(Request $request, string $platform): JsonResponse
    {
        $excluded = $request->input('excluded', []);
        if (is_string($excluded)) {
            $excluded = array_filter(explode(',', $excluded));
        }

        $game = match ($platform) {
            'nintendo-switch' => $this->getRecommendedNintendoSwitchGame($excluded),
            'playstation' => $this->getRecommendedPlayStationGame($excluded),
            'steam' => $this->getRecommendedSteamGame($excluded),
            default => null,
        };

        if (! $game) {
            return response()->json([
                'success' => false,
                'message' => 'Geen games gevonden om aan te bevelen.',
            ]);
        }

        return response()->json([
            'success' => true,
            'game' => $game,
        ]);
    }

    public function accept(Request $request, string $platform, int $id): JsonResponse
    {
        $game = match ($platform) {
            'nintendo-switch' => NintendoSwitchGame::find($id),
            'playstation' => PlayStationGame::find($id),
            'steam' => SteamGame::find($id),
            default => null,
        };

        if (! $game) {
            return response()->json([
                'success' => false,
                'message' => 'Game niet gevonden.',
            ], 404);
        }

        $game->update(['backlog_status' => BacklogStatus::Playing]);

        return response()->json([
            'success' => true,
            'message' => 'Game toegevoegd aan "Nu aan het spelen"!',
        ]);
    }

    private function getRecommendedNintendoSwitchGame(array $excluded): ?array
    {
        $games = NintendoSwitchGame::query()
            ->withSum('sessions', 'duration_minutes')
            ->where('main_story_completed', false)
            ->where(function ($q) {
                $q->whereNull('backlog_status')
                    ->orWhereNotIn('backlog_status', [BacklogStatus::Completed, BacklogStatus::Dropped]);
            })
            ->when(count($excluded) > 0, fn ($q) => $q->whereNotIn('id', $excluded))
            ->get()
            ->filter(function ($game) {
                $hours = ($game->sessions_sum_duration_minutes ?? 0) / 60;

                return $hours <= 50;
            });

        return $this->selectBestGame($games, 'nintendo-switch');
    }

    private function getRecommendedPlayStationGame(array $excluded): ?array
    {
        $games = PlayStationGame::query()
            ->withSum('sessions', 'duration_minutes')
            ->where('main_story_completed', false)
            ->where(function ($q) {
                $q->whereNull('backlog_status')
                    ->orWhereNotIn('backlog_status', [BacklogStatus::Completed, BacklogStatus::Dropped]);
            })
            ->when(count($excluded) > 0, fn ($q) => $q->whereNotIn('id', $excluded))
            ->get()
            ->filter(function ($game) {
                $sessionMinutes = $game->sessions_sum_duration_minutes ?? 0;
                $manualMinutes = $game->manual_minutes ?? 0;
                $hours = ($sessionMinutes + $manualMinutes) / 60;

                return $hours <= 50;
            });

        return $this->selectBestGame($games, 'playstation');
    }

    private function getRecommendedSteamGame(array $excluded): ?array
    {
        $games = SteamGame::query()
            ->where('main_story_completed', false)
            ->where('playtime_minutes', '<=', 50 * 60)
            ->where(function ($q) {
                $q->whereNull('backlog_status')
                    ->orWhereNotIn('backlog_status', [BacklogStatus::Completed, BacklogStatus::Dropped]);
            })
            ->when(count($excluded) > 0, fn ($q) => $q->whereNotIn('id', $excluded))
            ->get();

        return $this->selectBestGame($games, 'steam');
    }

    private function selectBestGame($games, string $platform): ?array
    {
        if ($games->isEmpty()) {
            return null;
        }

        $scoredGames = $games->map(function ($game) use ($platform) {
            $playtimeHours = $this->getPlaytimeHours($game, $platform);
            $price = (float) ($game->price ?? 0);
            $lastPlayed = $game->last_played_at;

            // Base score: price / (playtime + 1) - higher price with less playtime = higher score
            $score = $price / ($playtimeHours + 1);

            // Days since last played multiplier (more days = higher priority)
            if ($lastPlayed) {
                $daysSinceLastPlayed = Carbon::parse($lastPlayed)->diffInDays(now());
                $score *= (1 + ($daysSinceLastPlayed / 365)); // Up to 2x multiplier for year-old games
            } else {
                // Never played - high priority
                $score *= 2;
            }

            // Backlog status bonuses
            if ($game->backlog_status === BacklogStatus::WantToPlay) {
                $score += 50;
            } elseif ($game->backlog_status === BacklogStatus::Playing) {
                $score += 25;
            }

            // Zero playtime bonus
            if ($playtimeHours < 0.5) {
                $score += 30;
            }

            return [
                'game' => $game,
                'score' => $score,
                'playtime_hours' => $playtimeHours,
            ];
        });

        // Get the game with the highest score
        $best = $scoredGames->sortByDesc('score')->first();

        if (! $best) {
            return null;
        }

        $game = $best['game'];

        return [
            'id' => $game->id,
            'name' => $game->name,
            'image_url' => $game->image_url,
            'price' => $game->price,
            'playtime_hours' => round($best['playtime_hours'], 1),
            'last_played_at' => $game->last_played_at?->format('d M Y'),
            'last_played_ago' => $game->last_played_at?->diffForHumans(),
            'backlog_status' => $game->backlog_status?->value,
            'backlog_status_label' => $game->backlog_status?->label(),
            'platform' => $game->platform ?? null,
            'score' => round($best['score'], 1),
        ];
    }

    private function getPlaytimeHours($game, string $platform): float
    {
        return match ($platform) {
            'nintendo-switch' => ($game->sessions_sum_duration_minutes ?? 0) / 60,
            'playstation' => (($game->sessions_sum_duration_minutes ?? 0) + ($game->manual_minutes ?? 0)) / 60,
            'steam' => ($game->playtime_minutes ?? 0) / 60,
            default => 0,
        };
    }
}
