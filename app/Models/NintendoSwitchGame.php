<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NintendoSwitchGame extends Model
{
    protected $fillable = [
        'name',
        'image_url',
        'hours',
        'sessions',
        'avg_session_minutes',
        'last_played_at',
    ];

    protected function casts(): array
    {
        return [
            'hours' => 'decimal:1',
            'sessions' => 'integer',
            'avg_session_minutes' => 'integer',
            'last_played_at' => 'date',
        ];
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(NintendoSwitchSession::class);
    }

    public function scopeMostPlayed($query, int $limit = 10)
    {
        return $query->orderByDesc('hours')->limit($limit);
    }

    public function scopeRecentlyPlayed($query, int $limit = 10)
    {
        return $query->orderByDesc('last_played_at')->limit($limit);
    }

    public function getFormattedHoursAttribute(): string
    {
        return number_format($this->calculated_hours, 1).'h';
    }

    public function getCalculatedHoursAttribute(): float
    {
        if ($this->attributes['sessions_sum_duration_minutes'] ?? null) {
            return round($this->attributes['sessions_sum_duration_minutes'] / 60, 1);
        }

        return round($this->sessions()->sum('duration_minutes') / 60, 1);
    }

    public function getCalculatedSessionsAttribute(): int
    {
        if (isset($this->attributes['sessions_count'])) {
            return (int) $this->attributes['sessions_count'];
        }

        return $this->sessions()->count();
    }

    public function getFormattedAvgSessionAttribute(): string
    {
        if (! $this->avg_session_minutes) {
            return '-';
        }

        $hours = floor($this->avg_session_minutes / 60);
        $minutes = $this->avg_session_minutes % 60;

        return $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
    }
}
