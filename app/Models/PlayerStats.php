<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerStats extends Model
{
    /** @use HasFactory<\Database\Factories\PlayerStatsFactory> */
    use HasFactory;

    protected $fillable = [
        'total_xp',
        'level',
        'current_streak',
        'longest_streak',
        'last_active_date',
        'tasks_completed',
    ];

    protected function casts(): array
    {
        return [
            'last_active_date' => 'date',
        ];
    }

    public static function instance(): static
    {
        return static::firstOrCreate([], [
            'total_xp' => 0,
            'level' => 1,
            'current_streak' => 0,
            'longest_streak' => 0,
            'tasks_completed' => 0,
        ]);
    }

    public function xpForNextLevel(): int
    {
        return (int) (100 * pow(1.6, $this->level - 1));
    }

    public function xpIntoCurrentLevel(): int
    {
        $xpAtCurrentLevel = $this->totalXpForLevel($this->level);

        return max(0, $this->total_xp - $xpAtCurrentLevel);
    }

    public function xpProgressPercent(): int
    {
        $needed = $this->xpForNextLevel();

        return $needed > 0 ? (int) min(100, ($this->xpIntoCurrentLevel() / $needed) * 100) : 100;
    }

    public function levelName(): string
    {
        return match (true) {
            $this->level >= 20 => 'Legende',
            $this->level >= 15 => 'Meester',
            $this->level >= 10 => 'Veteraan',
            $this->level >= 7 => 'Avonturier',
            $this->level >= 5 => 'Ontdekker',
            $this->level >= 3 => 'Leerling',
            default => 'Beginner',
        };
    }

    public function recalculateLevelFromXp(): void
    {
        $level = 1;
        while ($this->total_xp >= $this->totalXpForLevel($level + 1)) {
            $level++;
        }
        $this->level = $level;
    }

    private function totalXpForLevel(int $level): int
    {
        $total = 0;
        for ($i = 1; $i < $level; $i++) {
            $total += (int) (100 * pow(1.6, $i - 1));
        }

        return $total;
    }
}
