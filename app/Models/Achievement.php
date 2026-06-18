<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Achievement extends Model
{
    /** @use HasFactory<\Database\Factories\AchievementFactory> */
    use HasFactory;

    protected $fillable = ['key', 'name', 'description', 'icon', 'xp_bonus'];

    public function unlocked(): HasOne
    {
        return $this->hasOne(UnlockedAchievement::class);
    }
}
