<?php

namespace App\Models;

use App\Enums\IntelligenceLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScambaiterConversation extends Model
{
    protected $fillable = [
        'title',
        'scammer_name',
        'subject',
        'occupation',
        'intelligence_level',
        'personality_traits',
        'personal_details',
        'writing_style',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'intelligence_level' => IntelligenceLevel::class,
            'personality_traits' => 'array',
            'is_locked' => 'boolean',
        ];
    }

    public function emails(): HasMany
    {
        return $this->hasMany(ScambaiterEmail::class)->orderBy('created_at');
    }
}
