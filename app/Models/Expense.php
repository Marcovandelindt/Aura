<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Expense extends Model
{
    protected $fillable = [
        'expense_category_id',
        'expense_subcategory_id',
        'merchant_id',
        'subscription_id',
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

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseSubcategory::class, 'expense_subcategory_id');
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'expense_tag');
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
