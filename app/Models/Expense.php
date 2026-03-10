<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'expense_category_id',
        'amount',
        'date',
        'description',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
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

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderByDesc('date')->orderByDesc('created_at')->limit($limit);
    }

    public function scopeBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function getFormattedAmountAttribute(): string
    {
        return '€'.number_format($this->amount, 2, ',', '.');
    }
}
