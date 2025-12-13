<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNintendoSwitchSessionRequest;
use App\Http\Requests\UpdateNintendoSwitchSessionRequest;
use App\Models\NintendoSwitchGame;
use App\Models\NintendoSwitchSession;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;

class NintendoSwitchSessionController extends Controller
{
    public function store(StoreNintendoSwitchSessionRequest $request, NintendoSwitchGame $game): RedirectResponse
    {
        $playedAt = Carbon::parse($request->played_at);
        $durationMinutes = $request->duration_minutes;

        $game->sessions()->create([
            'started_at' => $playedAt,
            'duration_minutes' => $durationMinutes,
        ]);

        $this->updateGameStats($game);

        return redirect()->route('nintendo-switch.games.show', $game)
            ->with('success', 'Session added successfully.');
    }

    public function update(UpdateNintendoSwitchSessionRequest $request, NintendoSwitchSession $session): RedirectResponse
    {
        $playedAt = Carbon::parse($request->played_at);
        $durationMinutes = $request->duration_minutes;

        $session->update([
            'started_at' => $playedAt,
            'duration_minutes' => $durationMinutes,
        ]);

        $this->updateGameStats($session->game);

        return redirect()->route('nintendo-switch.games.show', $session->game)
            ->with('success', 'Session updated successfully.');
    }

    public function destroy(NintendoSwitchSession $session): RedirectResponse
    {
        $game = $session->game;
        $session->delete();

        $this->updateGameStats($game);

        return redirect()->route('nintendo-switch.games.show', $game)
            ->with('success', 'Session deleted successfully.');
    }

    private function updateGameStats(NintendoSwitchGame $game): void
    {
        $totalMinutes = $game->sessions()->sum('duration_minutes');
        $sessionCount = $game->sessions()->count();

        $game->update([
            'hours' => round($totalMinutes / 60, 1),
            'sessions' => $sessionCount,
            'avg_session_minutes' => $sessionCount > 0 ? round($totalMinutes / $sessionCount) : null,
            'last_played_at' => $game->sessions()->max('started_at'),
        ]);
    }
}
