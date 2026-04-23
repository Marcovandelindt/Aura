<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JournalTag extends Model
{
    /** @use HasFactory<\Database\Factories\JournalTagFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
    ];

    public function entries(): BelongsToMany
    {
        return $this->belongsToMany(JournalEntry::class, 'journal_entry_tag');
    }
}
