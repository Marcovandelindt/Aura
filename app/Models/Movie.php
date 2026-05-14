<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    /** @use HasFactory<\Database\Factories\MovieFactory> */
    use HasFactory;

    protected $fillable = [
        'tmdb_id',
        'title',
        'original_title',
        'overview',
        'poster_path',
        'backdrop_path',
        'release_date',
        'runtime',
        'vote_average',
        'vote_count',
        'genres',
        'status',
        'original_language',
        'watch_count',
        'last_watched_at',
        'first_watched_at',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'genres' => 'array',
            'last_watched_at' => 'datetime',
            'first_watched_at' => 'datetime',
        ];
    }

    public function watches(): HasMany
    {
        return $this->hasMany(MovieWatch::class);
    }

    public function people(): BelongsToMany
    {
        return $this->belongsToMany(Person::class)
            ->withPivot(['character', 'department', 'job', 'cast_order'])
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

    public function incrementWatchCount(): void
    {
        $this->increment('watch_count');

        $latestWatch = $this->watches()->latest('watched_at')->first();
        $this->last_watched_at = $latestWatch?->watched_at ?? now();

        if (! $this->first_watched_at) {
            $earliestWatch = $this->watches()->oldest('watched_at')->first();
            $this->first_watched_at = $earliestWatch?->watched_at ?? now();
        }

        $this->save();
    }
}
