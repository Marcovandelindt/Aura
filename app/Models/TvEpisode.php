<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TvEpisode extends Model
{
    /** @use HasFactory<\Database\Factories\TvEpisodeFactory> */
    use HasFactory;

    protected $fillable = [
        'tv_season_id',
        'tmdb_id',
        'name',
        'overview',
        'still_path',
        'episode_number',
        'air_date',
        'runtime',
        'vote_average',
        'vote_count',
    ];

    protected function casts(): array
    {
        return [
            'air_date' => 'date',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(TvSeason::class, 'tv_season_id');
    }

    public function watches(): HasMany
    {
        return $this->hasMany(EpisodeWatch::class);
    }

    public function stillUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->still_path
                ? config('tmdb.image_base_url').config('tmdb.backdrop_sizes.medium').$this->still_path
                : null
        );
    }

    public function addWatch(): void
    {
        EpisodeWatch::create([
            'tv_episode_id' => $this->id,
            'watched_at' => now(),
        ]);

        $this->season->updateProgress();
        $this->season->series->recordWatch();
    }

    public function watched(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->watches()->exists()
        );
    }

    public function watchCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->watches()->count()
        );
    }
}
