<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NintendoSwitchSession extends Model
{
    protected $fillable = [
        'nintendo_switch_game_id',
        'started_at',
        'duration_minutes',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'duration_minutes' => 'integer',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(NintendoSwitchGame::class, 'nintendo_switch_game_id');
    }

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderByDesc('started_at')->limit($limit);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('started_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->where('started_at', '>=', now()->startOfWeek());
    }

    public function getFormattedDurationAttribute(): string
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }
}
