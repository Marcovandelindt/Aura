<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovieWatch extends Model
{
    protected $fillable = [
        'movie_id',
        'watched_at',
        'year_only',
        'notes',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'watched_at' => 'datetime',
            'year_only' => 'boolean',
        ];
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }
}
