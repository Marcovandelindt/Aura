<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class LastfmScrobble extends Model
{
    protected $fillable = [
        'track_name',
        'artist_name',
        'album_name',
        'album_image_url',
        'played_at',
        'spotify_track_id',
        'duration_ms',
        'popularity',
        'spotify_uri',
        'enriched_at',
        'enrichment_confidence',
    ];

    protected function casts(): array
    {
        return [
            'played_at' => 'datetime',
            'enriched_at' => 'datetime',
            'duration_ms' => 'integer',
            'popularity' => 'integer',
            'enrichment_confidence' => 'integer',
        ];
    }

    public function getFormattedDurationAttribute(): string
    {
        if (! $this->duration_ms) {
            return '--:--';
        }

        $seconds = $this->duration_ms / 1000;
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%d:%02d', $minutes, $remainingSeconds);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('played_at', Carbon::today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('played_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek(),
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('played_at', Carbon::now()->month)
            ->whereYear('played_at', Carbon::now()->year);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('played_at', $year);
    }
}
