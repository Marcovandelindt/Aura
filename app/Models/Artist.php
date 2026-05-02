<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Artist extends Model
{
    protected $fillable = ['name', 'spotify_artist_id', 'image_url'];

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'track_artists')
            ->withPivot('is_primary', 'sort_order')
            ->orderByPivot('sort_order');
    }
}
