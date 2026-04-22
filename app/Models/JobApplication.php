<?php

namespace App\Models;

use App\Enums\JobApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    /** @use HasFactory<\Database\Factories\JobApplicationFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'company',
        'location',
        'url',
        'status',
        'notes',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => JobApplicationStatus::class,
            'applied_at' => 'date',
        ];
    }
}
