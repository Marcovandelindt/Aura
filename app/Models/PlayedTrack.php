<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
     * Get moods for this track (via spotify_track_id)
     */
    public function moods(): BelongsToMany
    {
        return $this->belongsToMany(Mood::class, 'played_track_mood', 'spotify_track_id', 'mood_id', 'spotify_track_id', 'id');
    }

    /**
     * Get moods for a specific spotify track ID
     */
    public static function getMoodsForTrack(string $spotifyTrackId)
    {
        return Mood::whereHas('tracks', function($query) use ($spotifyTrackId) {
            $query->where('played_track_mood.spotify_track_id', $spotifyTrackId);
        })->get();
    }

    /**
     * Get moods for multiple tracks (bulk loading)
     */
    public static function getMoodsForTracks(array $spotifyTrackIds): array
    {
        if (empty($spotifyTrackIds)) {
            return [];
        }

        $moodsData = \Illuminate\Support\Facades\DB::table('played_track_mood')
            ->join('moods', 'played_track_mood.mood_id', '=', 'moods.id')
            ->whereIn('played_track_mood.spotify_track_id', $spotifyTrackIds)
            ->where('moods.is_active', true)
            ->select([
                'played_track_mood.spotify_track_id',
                'moods.id',
                'moods.name',
                'moods.color',
                'moods.icon'
            ])
            ->get();

        // Group by track ID
        $result = [];
        foreach ($moodsData as $moodData) {
            if (!isset($result[$moodData->spotify_track_id])) {
                $result[$moodData->spotify_track_id] = [];
            }
            $result[$moodData->spotify_track_id][] = [
                'id' => $moodData->id,
                'name' => $moodData->name,
                'color' => $moodData->color,
                'icon' => $moodData->icon
            ];
        }

        return $result;
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
