<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PlayedTrack extends Model
{
    protected $fillable = [
        'spotify_track_id',
        'played_at',
        'track_name',
        'artist_names',
        'album_name',
        'album_image_url',
        'duration_ms',
        'popularity',
        'preview_url',
        'spotify_uri',
        'contexts',
    ];

    protected $casts = [
        'played_at' => 'datetime',
        'artist_names' => 'array',
        'contexts' => 'array',
        'duration_ms' => 'integer',
        'popularity' => 'integer',
    ];

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        $seconds = $this->duration_ms / 1000;
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        
        return sprintf('%d:%02d', $minutes, $remainingSeconds);
    }

    /**
     * Get primary artist
     */
    public function getPrimaryArtistAttribute(): string
    {
        return $this->artist_names[0] ?? 'Unknown Artist';
    }

    /**
     * Get all artists as string
     */
    public function getArtistsStringAttribute(): string
    {
        return implode(', ', $this->artist_names);
    }

    /**
     * Get relative played at time
     */
    public function getPlayedAtHumanAttribute(): string
    {
        return $this->played_at->diffForHumans();
    }

    /**
     * Scope for tracks played today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('played_at', Carbon::today());
    }

    /**
     * Scope for tracks played this week
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('played_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    /**
     * Scope for tracks played this month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('played_at', Carbon::now()->month)
                     ->whereYear('played_at', Carbon::now()->year);
    }

    /**
     * Get most played tracks
     */
    public static function mostPlayed($limit = 10, $period = null)
    {
        $query = self::select('spotify_track_id', 'track_name', 'artist_names', 'album_name', 'album_image_url')
            ->selectRaw('COUNT(*) as play_count')
            ->groupBy('spotify_track_id', 'track_name', 'artist_names', 'album_name', 'album_image_url');

        if ($period === 'today') {
            $query->today();
        } elseif ($period === 'week') {
            $query->thisWeek();
        } elseif ($period === 'month') {
            $query->thisMonth();
        }

        return $query->orderBy('play_count', 'desc')
            ->limit($limit)
            ->get();
    }
}
