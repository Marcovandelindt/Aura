<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JournalEntry extends Model
{
    /** @use HasFactory<\Database\Factories\JournalEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'date',
        'rating',
        'mood_id',
        'location',
        'word_count',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'rating' => 'integer',
            'word_count' => 'integer',
        ];
    }

    public function mood(): BelongsTo
    {
        return $this->belongsTo(Mood::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(JournalTag::class, 'journal_entry_tag');
    }

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderByDesc('date')->limit($limit);
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }

    public function getExcerptAttribute(): string
    {
        return \Str::limit(strip_tags($this->content ?? ''), 160);
    }

    public function getRatingColorAttribute(): string
    {
        return match (true) {
            $this->rating >= 8 => '#10b981',
            $this->rating >= 6 => '#f59e0b',
            $this->rating >= 4 => '#f97316',
            default => '#ef4444',
        };
    }

    public static function calculateStreak(): int
    {
        $streak = 0;
        $date = now()->startOfDay();

        while (true) {
            $exists = static::whereDate('date', $date)->exists();

            if (! $exists) {
                if ($streak === 0 && $date->isToday()) {
                    $date->subDay();

                    continue;
                }
                break;
            }

            $streak++;
            $date->subDay();
        }

        return $streak;
    }
}
