<?php

namespace App\Http\Controllers\Gaming;

use App\Enums\BacklogStatus;
use App\Http\Controllers\Controller;
use App\Models\NintendoSwitchGame;
use App\Models\PlayStationGame;
use App\Models\SteamGame;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class BacklogController extends Controller
{
    public function index(Request $request): View
    {
        $statusFilter = $request->get('status');
        $platformFilter = $request->get('platform');

        $games = $this->getAllBacklogGames($statusFilter, $platformFilter);

        $stats = [
            'total' => $games->count(),
            'want_to_play' => $games->where('backlog_status', BacklogStatus::WantToPlay)->count(),
            'playing' => $games->where('backlog_status', BacklogStatus::Playing)->count(),
            'completed' => $games->where('backlog_status', BacklogStatus::Completed)->count(),
            'dropped' => $games->where('backlog_status', BacklogStatus::Dropped)->count(),
        ];

        return view('backlog.index', [
            'games' => $games,
            'stats' => $stats,
            'statusFilter' => $statusFilter,
            'platformFilter' => $platformFilter,
            'statuses' => BacklogStatus::cases(),
        ]);
    }

    public function updateStatus(Request $request, string $type, int $id): RedirectResponse
    {
        $request->validate([
            'status' => ['nullable', 'string'],
        ]);

        $model = $this->getModelByType($type);

        if (! $model) {
            return back()->with('error', 'Ongeldig platform type.');
        }

        $game = $model::findOrFail($id);

        $status = $request->get('status');

        if ($status === '' || $status === null) {
            $game->backlog_status = null;
        } else {
            $game->backlog_status = BacklogStatus::from($status);
        }

        $game->save();

        $message = $game->backlog_status
            ? "'{$game->name}' status gewijzigd naar {$game->backlog_status->label()}."
            : "'{$game->name}' verwijderd uit backlog.";

        return back()->with('success', $message);
    }

    private function getAllBacklogGames(?string $statusFilter, ?string $platformFilter): Collection
    {
        $games = collect();

        $platforms = $platformFilter
            ? [$platformFilter]
            : ['playstation', 'nintendo-switch', 'steam'];

        foreach ($platforms as $platform) {
            $query = match ($platform) {
                'playstation' => PlayStationGame::query(),
                'nintendo-switch' => NintendoSwitchGame::query(),
                'steam' => SteamGame::query(),
                default => null,
            };

            if (! $query) {
                continue;
            }

            $query->inBacklog();

            if ($statusFilter) {
                $query->where('backlog_status', $statusFilter);
            }

            $platformGames = $query->get()->map(function ($game) use ($platform) {
                $game->platform_type = $platform;

                return $game;
            });

            $games = $games->merge($platformGames);
        }

        return $games->sortBy(function ($game) {
            $order = [
                BacklogStatus::Playing->value => 0,
                BacklogStatus::WantToPlay->value => 1,
                BacklogStatus::Completed->value => 2,
                BacklogStatus::Dropped->value => 3,
            ];

            return $order[$game->backlog_status->value] ?? 99;
        });
    }

    private function getModelByType(string $type): ?string
    {
        return match ($type) {
            'playstation' => PlayStationGame::class,
            'nintendo-switch' => NintendoSwitchGame::class,
            'steam' => SteamGame::class,
            default => null,
        };
    }
}
