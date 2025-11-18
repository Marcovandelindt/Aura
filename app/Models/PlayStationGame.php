<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlayStationGame extends Model
{
    public function sessions(): HasMany
    {
        return $this->hasMany(PlayStationSession::class);
    }

    protected $fillable = [
        'name',
        'platform',
        'image_url',
        'hours',
        'sessions',
        'avg_session_minutes',
        'last_played_at',
        'trophies',
        'completion_percentage',
        'psn_url',
    ];

    protected function casts(): array
    {
        return [
            'hours' => 'decimal:1',
            'sessions' => 'integer',
            'avg_session_minutes' => 'integer',
            'last_played_at' => 'date',
            'trophies' => 'integer',
            'completion_percentage' => 'decimal:2',
        ];
    }

    public function scopeMostPlayed($query, int $limit = 10)
    {
        return $query->orderByDesc('hours')->limit($limit);
    }

    public function scopeRecentlyPlayed($query, int $limit = 10)
    {
        return $query->orderByDesc('last_played_at')->limit($limit);
    }

    public function scopeByPlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    public function getFormattedHoursAttribute(): string
    {
        return number_format($this->hours, 1).'h';
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
