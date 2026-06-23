<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = [
        'name',
    ];

    public function expenses(): BelongsToMany
    {
        return $this->belongsToMany(Expense::class, 'expense_tag');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }
}
