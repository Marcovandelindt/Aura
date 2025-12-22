<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Genre extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    protected static function booted(): void
    {
        static::creating(function (Genre $genre) {
            if (empty($genre->slug)) {
                $genre->slug = Str::slug($genre->name);
            }
        });
    }

    public function nintendoSwitchGames(): BelongsToMany
    {
        return $this->belongsToMany(NintendoSwitchGame::class);
    }

    public function playStationGames(): BelongsToMany
    {
        return $this->belongsToMany(PlayStationGame::class);
    }

    public function steamGames(): BelongsToMany
    {
        return $this->belongsToMany(SteamGame::class);
    }
}
