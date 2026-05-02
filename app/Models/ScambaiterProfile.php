<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScambaiterProfile extends Model
{
    protected $fillable = [
        'full_name',
        'age',
        'nationality',
        'location',
        'date_of_birth',
        'additional_facts',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'age' => 'integer',
        ];
    }

    public static function getInstance(): self
    {
        return self::firstOrCreate([]);
    }
}
