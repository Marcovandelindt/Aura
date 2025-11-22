<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayedTrackMood extends Model
{
    protected $table = 'played_track_mood';

    protected $fillable = [
        'spotify_track_id',
        'mood_id',
        'game_id',
    ];

    protected $casts = [
        'mood_id' => 'integer',
        'game_id' => 'integer',
    ];

    public function mood(): BelongsTo
    {
        return $this->belongsTo(Mood::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
