<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Track extends Model
{
    protected $fillable = [
        'spotify_track_id',
        'track_name',
        'artist_names',
        'artist_ids',
        'album_name',
        'album_image_url',
        'duration_ms',
        'popularity',
        'preview_url',
        'spotify_uri',
        'genres',
    ];

    protected $casts = [
        'artist_names' => 'array',
        'artist_ids' => 'array',
        'genres' => 'array',
        'duration_ms' => 'integer',
        'popularity' => 'integer',
    ];

    /**
     * Get all played instances of this track
     */
    public function playedTracks(): HasMany
    {
        return $this->hasMany(PlayedTrack::class);
    }

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
     * Get all genres as string
     */
    public function getGenresStringAttribute(): string
    {
        return implode(', ', $this->genres ?? []);
    }

    /**
     * Get primary genre
     */
    public function getPrimaryGenreAttribute(): ?string
    {
        return $this->genres[0] ?? null;
    }

    /**
     * Find or create track by Spotify ID
     */
    public static function findOrCreateBySpotifyId(string $spotifyTrackId, array $trackData): self
    {
        return self::firstOrCreate(
            ['spotify_track_id' => $spotifyTrackId],
            $trackData
        );
    }
}
