<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Mood extends Model
{
    protected $fillable = [
        'name',
        'color',
        'icon',
        'description',
        'is_active',
        'usage_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'usage_count' => 'integer',
    ];

    /**
     * Get all tracks that have this mood (via spotify_track_id)
     */
    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(PlayedTrack::class, 'played_track_mood', 'mood_id', 'spotify_track_id', 'id', 'spotify_track_id');
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Scope for active moods only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordering by usage (most used first)
     */
    public function scopePopular($query)
    {
        return $query->orderBy('usage_count', 'desc');
    }

    /**
     * Get tracks by spotify track ID
     */
    public static function getTracksWithMoods(string $spotifyTrackId)
    {
        return self::whereHas('tracks', function($query) use ($spotifyTrackId) {
            $query->where('spotify_track_id', $spotifyTrackId);
        })->get();
    }
}
