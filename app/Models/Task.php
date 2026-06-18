<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category',
        'difficulty',
        'type',
        'due_date',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function xpReward(): int
    {
        return match ($this->difficulty) {
            'easy' => 10,
            'normal' => 25,
            'hard' => 60,
        };
    }

    public function categoryLabel(): string
    {
        return match ($this->category) {
            'huis' => '🏠 Huis',
            'beweging' => '🚶 Beweging',
            'welzijn' => '🧘 Welzijn',
            'persoonlijk' => '🎯 Persoonlijk',
            'werk' => '💼 Werk',
            'overig' => '⭐ Overig',
        };
    }

    public function difficultyLabel(): string
    {
        return match ($this->difficulty) {
            'easy' => 'Makkelijk',
            'normal' => 'Normaal',
            'hard' => 'Zwaar',
        };
    }

    public function scopePending(Builder $query): void
    {
        $query->whereNull('completed_at');
    }

    public function scopeForToday(Builder $query): void
    {
        $query->whereNull('completed_at')
            ->where(function (Builder $q) {
                $q->where('type', 'once')
                    ->orWhere('type', 'daily')
                    ->orWhere(function (Builder $q2) {
                        $q2->where('type', 'weekly')
                            ->where('due_date', '<=', now()->endOfWeek());
                    });
            });
    }
}
