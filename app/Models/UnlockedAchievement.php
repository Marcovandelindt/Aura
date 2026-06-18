<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnlockedAchievement extends Model
{
    /** @use HasFactory<\Database\Factories\UnlockedAchievementFactory> */
    use HasFactory;

    protected $fillable = ['achievement_id', 'unlocked_at'];

    protected function casts(): array
    {
        return [
            'unlocked_at' => 'datetime',
        ];
    }

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }
}
