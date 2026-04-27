<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LastfmTrackCorrection extends Model
{
    protected $fillable = ['track_name', 'artist_name', 'all_artist_names'];

    protected function casts(): array
    {
        return [
            'all_artist_names' => 'array',
        ];
    }
}
