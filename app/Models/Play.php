<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Play extends Model
{
    protected $fillable = ['track_id', 'played_at', 'source', 'context'];

    protected function casts(): array
    {
        return [
            'played_at' => 'datetime',
            'context' => 'array',
        ];
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }
}
