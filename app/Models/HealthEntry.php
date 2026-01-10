<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthEntry extends Model
{
    public static function getStepGoal(): int
    {
        return (int) Setting::get('health_step_goal', 10000);
    }

    protected $fillable = [
        'date',
        'steps',
        'avg_heart_rate',
        'resting_heart_rate',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'steps' => 'integer',
            'avg_heart_rate' => 'integer',
            'resting_heart_rate' => 'integer',
        ];
    }

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderByDesc('date')->limit($limit);
    }

    public function scopeThisWeek($query)
    {
        return $query->where('date', '>=', now()->startOfWeek());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', now()->month)
            ->whereYear('date', now()->year);
    }

    public function scopeBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function getFormattedStepsAttribute(): string
    {
        return $this->steps ? number_format($this->steps) : '-';
    }

    public function getFormattedAvgHeartRateAttribute(): string
    {
        return $this->avg_heart_rate ? $this->avg_heart_rate.' bpm' : '-';
    }

    public function getFormattedRestingHeartRateAttribute(): string
    {
        return $this->resting_heart_rate ? $this->resting_heart_rate.' bpm' : '-';
    }

    public function meetsStepGoal(?int $goal = null): bool
    {
        $goal = $goal ?? self::getStepGoal();

        return $this->steps && $this->steps >= $goal;
    }
}
