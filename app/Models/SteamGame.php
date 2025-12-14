<?php

namespace App\Models;

use App\Enums\BacklogStatus;
use App\Models\Concerns\HasBacklogStatus;
use Illuminate\Database\Eloquent\Model;

class SteamGame extends Model
{
    use HasBacklogStatus;

    protected $fillable = [
        'steam_appid',
        'name',
        'image_url',
        'playtime_minutes',
        'playtime_2weeks_minutes',
        'price',
        'last_played_at',
        'backlog_status',
    ];

    protected function casts(): array
    {
        return [
            'steam_appid' => 'integer',
            'playtime_minutes' => 'integer',
            'playtime_2weeks_minutes' => 'integer',
            'price' => 'decimal:2',
            'last_played_at' => 'datetime',
            'backlog_status' => BacklogStatus::class,
        ];
    }

    public function scopeMostPlayed($query, int $limit = 10)
    {
        return $query->orderByDesc('playtime_minutes')->limit($limit);
    }

    public function scopeRecentlyPlayed($query, int $limit = 10)
    {
        return $query->whereNotNull('last_played_at')
            ->orderByDesc('last_played_at')
            ->limit($limit);
    }

    public function scopePlayedRecently($query)
    {
        return $query->whereNotNull('playtime_2weeks_minutes')
            ->where('playtime_2weeks_minutes', '>', 0);
    }

    public function scopeNeverPlayed($query)
    {
        return $query->where('playtime_minutes', 0);
    }

    public function getPlaytimeHoursAttribute(): float
    {
        return round($this->playtime_minutes / 60, 1);
    }

    public function getFormattedPlaytimeAttribute(): string
    {
        $hours = floor($this->playtime_minutes / 60);
        $minutes = $this->playtime_minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }

    public function getPlaytime2weeksHoursAttribute(): ?float
    {
        if ($this->playtime_2weeks_minutes === null) {
            return null;
        }

        return round($this->playtime_2weeks_minutes / 60, 1);
    }

    public function getSteamUrlAttribute(): string
    {
        return "https://store.steampowered.com/app/{$this->steam_appid}";
    }
}
