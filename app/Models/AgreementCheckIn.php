<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgreementCheckIn extends Model
{
    /** @use HasFactory<\Database\Factories\AgreementCheckInFactory> */
    use HasFactory;

    protected $fillable = [
        'agreement_id',
        'checked_on',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'checked_on' => 'date',
        ];
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'respected' => 'Gehouden',
            'partially' => 'Deels',
            'not_respected' => 'Niet gehouden',
            default => '',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'respected' => '#10b981',
            'partially' => '#f59e0b',
            'not_respected' => '#ef4444',
            default => '#6b7280',
        };
    }
}
