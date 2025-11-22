<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Game extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'cover_image',
        'developer',
        'publisher',
        'release_year',
        'track_count',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'track_count' => 'integer',
        'release_year' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Game $game) {
            if (empty($game->slug)) {
                $game->slug = Str::slug($game->name);
            }
        });

        static::updating(function (Game $game) {
            if ($game->isDirty('name') && empty($game->slug)) {
                $game->slug = Str::slug($game->name);
            }
        });
    }

    public function trackMoods(): HasMany
    {
        return $this->hasMany(PlayedTrackMood::class);
    }

    public function incrementTrackCount(): void
    {
        $this->increment('track_count');
    }

    public function decrementTrackCount(): void
    {
        $this->decrement('track_count');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePopular($query)
    {
        return $query->orderBy('track_count', 'desc');
    }
}
