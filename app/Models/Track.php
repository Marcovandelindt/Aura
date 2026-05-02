<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Track extends Model
{
    protected $fillable = [
        'title',
        'spotify_track_id',
        'album_id',
        'duration_ms',
        'popularity',
        'preview_url',
        'spotify_uri',
    ];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'track_artists')
            ->withPivot('is_primary', 'sort_order')
            ->orderByPivot('sort_order');
    }

    public function plays(): HasMany
    {
        return $this->hasMany(Play::class);
    }

    public function moods(): BelongsToMany
    {
        return $this->belongsToMany(Mood::class, 'track_mood');
    }

    public function getPrimaryArtistAttribute(): ?Artist
    {
        return $this->artists->firstWhere('pivot.is_primary', true)
            ?? $this->artists->first();
    }

    public function getArtistsStringAttribute(): string
    {
        return $this->artists->pluck('name')->implode(', ');
    }

    public function getFormattedDurationAttribute(): string
    {
        if (! $this->duration_ms) {
            return '—';
        }

        $seconds = intdiv($this->duration_ms, 1000);

        return sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60);
    }
}
