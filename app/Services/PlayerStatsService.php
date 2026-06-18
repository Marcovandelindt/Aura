<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\PlayerStats;
use App\Models\Task;
use App\Models\UnlockedAchievement;

class PlayerStatsService
{
    public function recordCompletion(Task $task): array
    {
        $stats = PlayerStats::instance();

        $baseXp = $task->xpReward();
        $streakMultiplier = 1 + (min($stats->current_streak, 5) * 0.10);
        $earnedXp = (int) round($baseXp * $streakMultiplier);

        $this->updateStreak($stats);

        $stats->total_xp += $earnedXp;
        $stats->tasks_completed += 1;

        $levelBefore = $stats->level;

        $newAchievements = $this->checkAchievements($stats, $task);

        $stats->recalculateLevelFromXp();
        $stats->save();

        return [
            'earned_xp' => $earnedXp,
            'leveled_up' => $stats->level > $levelBefore,
            'new_achievements' => $newAchievements,
        ];
    }

    public function undoCompletion(Task $task): void
    {
        $stats = PlayerStats::instance();

        $stats->total_xp = max(0, $stats->total_xp - $task->xpReward());
        $stats->tasks_completed = max(0, $stats->tasks_completed - 1);
        $stats->recalculateLevelFromXp();
        $stats->save();
    }

    private function updateStreak(PlayerStats $stats): void
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        if ($stats->last_active_date?->toDateString() === $today) {
            return;
        }

        if ($stats->last_active_date?->toDateString() === $yesterday) {
            $stats->current_streak += 1;
        } else {
            $stats->current_streak = 1;
        }

        if ($stats->current_streak > $stats->longest_streak) {
            $stats->longest_streak = $stats->current_streak;
        }

        $stats->last_active_date = $today;
    }

    private function checkAchievements(PlayerStats $stats, Task $task): array
    {
        $unlockedKeys = UnlockedAchievement::with('achievement')
            ->get()
            ->pluck('achievement.key')
            ->toArray();

        $newlyUnlocked = [];

        $candidates = [
            'eerste_stap' => $stats->tasks_completed >= 1,
            'op_stoom' => $stats->current_streak >= 3,
            'doorzetter' => $stats->current_streak >= 7,
            'level_5' => $stats->level >= 5,
            'honderd' => $stats->tasks_completed >= 100,
            'huisbaas' => Task::where('category', 'huis')->whereNotNull('completed_at')->count() >= 10,
            'wandelaar' => Task::where('category', 'beweging')->whereNotNull('completed_at')->count() >= 10,
        ];

        foreach ($candidates as $key => $conditionMet) {
            if ($conditionMet && ! in_array($key, $unlockedKeys)) {
                $achievement = Achievement::where('key', $key)->first();
                if ($achievement) {
                    UnlockedAchievement::create([
                        'achievement_id' => $achievement->id,
                        'unlocked_at' => now(),
                    ]);

                    $stats->total_xp += $achievement->xp_bonus;
                    $newlyUnlocked[] = $achievement;
                }
            }
        }

        return $newlyUnlocked;
    }
}
