<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Idea extends Model
{
    protected $fillable = [
        'title',
        'description',
        'priority',
        'category',
        'is_completed',
        'completed_at'
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Mark idea as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => Carbon::now()
        ]);
    }

    /**
     * Mark idea as not completed
     */
    public function markAsNotCompleted(): void
    {
        $this->update([
            'is_completed' => false,
            'completed_at' => null
        ]);
    }

    /**
     * Toggle completion status
     */
    public function toggleCompletion(): void
    {
        if ($this->is_completed) {
            $this->markAsNotCompleted();
        } else {
            $this->markAsCompleted();
        }
    }

    /**
     * Scope for active (not completed) ideas
     */
    public function scopeActive($query)
    {
        return $query->where('is_completed', false);
    }

    /**
     * Scope for completed ideas
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope for priority
     */
    public function scopePriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for category
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
