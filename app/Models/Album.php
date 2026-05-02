<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Album extends Model
{
    protected $fillable = ['name', 'spotify_album_id', 'image_url', 'release_date'];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
        ];
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class);
    }
}
