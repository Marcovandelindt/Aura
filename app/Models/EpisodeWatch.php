<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EpisodeWatch extends Model
{
    protected $fillable = [
        'tv_episode_id',
        'watched_at',
        'notes',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'watched_at' => 'datetime',
        ];
    }

    public function episode(): BelongsTo
    {
        return $this->belongsTo(TvEpisode::class, 'tv_episode_id');
    }
}
