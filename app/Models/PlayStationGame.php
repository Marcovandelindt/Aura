<?php

namespace App\Models;

use App\Enums\BacklogStatus;
use App\Enums\PlayMode;
use App\Models\Concerns\HasBacklogStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlayStationGame extends Model
{
    use HasBacklogStatus;

    public function sessions(): HasMany
    {
        return $this->hasMany(PlayStationSession::class);
    }

    protected $fillable = [
        'name',
        'platform',
        'image_url',
        'price',
        'manual_minutes',
        'exclude_from_sync',
        'hours',
        'sessions',
        'avg_session_minutes',
        'last_played_at',
        'trophies',
        'completion_percentage',
        'psn_url',
        'backlog_status',
        'play_mode',
        'main_story_completed',
        'user_rating',
        'critic_rating',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'manual_minutes' => 'integer',
            'exclude_from_sync' => 'boolean',
            'hours' => 'decimal:1',
            'sessions' => 'integer',
            'avg_session_minutes' => 'integer',
            'last_played_at' => 'date',
            'trophies' => 'integer',
            'completion_percentage' => 'decimal:2',
            'backlog_status' => BacklogStatus::class,
            'play_mode' => PlayMode::class,
            'main_story_completed' => 'boolean',
            'user_rating' => 'integer',
            'critic_rating' => 'integer',
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
        return number_format($this->calculated_hours, 1).'h';
    }

    public function getCalculatedHoursAttribute(): float
    {
        $sessionMinutes = 0;

        // Use eager loaded sum if available, otherwise calculate from relationship
        if ($this->attributes['sessions_sum_duration_minutes'] ?? null) {
            $sessionMinutes = $this->attributes['sessions_sum_duration_minutes'];
        } else {
            $sessionMinutes = $this->sessions()->sum('duration_minutes');
        }

        // Add manual minutes if set
        $manualMinutes = $this->manual_minutes ?? 0;

        return round(($sessionMinutes + $manualMinutes) / 60, 1);
    }

    public function getFormattedManualTimeAttribute(): string
    {
        if (! $this->manual_minutes) {
            return '-';
        }

        $hours = floor($this->manual_minutes / 60);
        $minutes = $this->manual_minutes % 60;

        return $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
    }

    public function getCalculatedSessionsAttribute(): int
    {
        // Use eager loaded count if available, otherwise count from relationship
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

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }
}
