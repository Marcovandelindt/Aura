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
        'scammer_email_addresses',
        'subject',
        'occupation',
        'intelligence_level',
        'personality_traits',
        'personal_details',
        'writing_style',
        'backstory',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'intelligence_level' => IntelligenceLevel::class,
            'personality_traits' => 'array',
            'scammer_email_addresses' => 'array',
            'is_locked' => 'boolean',
        ];
    }

    public function emails(): HasMany
    {
        return $this->hasMany(ScambaiterEmail::class)->orderByRaw('COALESCE(sent_at, created_at) DESC');
    }
}
