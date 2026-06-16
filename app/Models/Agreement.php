<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agreement extends Model
{
    /** @use HasFactory<\Database\Factories\AgreementFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(AgreementCheckIn::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function checkInForDate(\Carbon\Carbon $date): ?AgreementCheckIn
    {
        return $this->checkIns()->whereDate('checked_on', $date)->first();
    }

    public function getLast30DaysCheckIns(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->checkIns()
            ->where('checked_on', '>=', now()->subDays(29)->startOfDay())
            ->orderBy('checked_on')
            ->get();
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'bereikbaarheid' => 'Bereikbaarheid',
            'faalangst' => 'Faalangst',
            'verantwoordelijkheid' => 'Verantwoordelijkheid',
            'perfectionisme' => 'Perfectionisme',
            default => 'Overig',
        };
    }
}
