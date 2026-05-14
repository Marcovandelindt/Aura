<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TvSeries extends Model
{
    /** @use HasFactory<\Database\Factories\TvSeriesFactory> */
    use HasFactory;

    protected $fillable = [
        'tmdb_id',
        'name',
        'name_en',
        'original_name',
        'overview',
        'poster_path',
        'backdrop_path',
        'first_air_date',
        'last_air_date',
        'vote_average',
        'vote_count',
        'genres',
        'status',
        'original_language',
        'number_of_seasons',
        'number_of_episodes',
        'episodes_watched',
        'completion_percentage',
        'last_watched_at',
        'first_watched_at',
    ];

    protected function casts(): array
    {
        return [
            'first_air_date' => 'date',
            'last_air_date' => 'date',
            'genres' => 'array',
            'last_watched_at' => 'datetime',
            'first_watched_at' => 'datetime',
        ];
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(TvSeason::class);
    }

    public function people(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'tv_series_person')
            ->withPivot(['character', 'department', 'job', 'cast_order', 'episode_count'])
            ->withTimestamps();
    }

    public function posterUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->poster_path
                ? config('tmdb.image_base_url').config('tmdb.poster_sizes.medium').$this->poster_path
                : null
        );
    }

    public function backdropUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->backdrop_path
                ? config('tmdb.image_base_url').config('tmdb.backdrop_sizes.large').$this->backdrop_path
                : null
        );
    }

    public function updateProgress(): void
    {
        $watchedCount = TvEpisode::query()
            ->whereHas('season', fn ($query) => $query->where('tv_series_id', $this->id))
            ->whereHas('watches')
            ->count();

        $this->episodes_watched = $watchedCount;

        $totalEpisodes = $this->number_of_episodes;

        if ($totalEpisodes > 0) {
            $this->completion_percentage = ($this->episodes_watched / $totalEpisodes) * 100;
        }

        $this->save();
    }

    public function recordWatch(): void
    {
        $this->last_watched_at = now();

        if (! $this->first_watched_at) {
            $this->first_watched_at = now();
        }

        $this->save();
        $this->updateProgress();
    }
}
