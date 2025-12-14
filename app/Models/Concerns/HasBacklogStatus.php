<?php

namespace App\Models\Concerns;

use App\Enums\BacklogStatus;
use Illuminate\Database\Eloquent\Builder;

trait HasBacklogStatus
{
    public function scopeInBacklog(Builder $query): Builder
    {
        return $query->whereNotNull('backlog_status');
    }

    public function scopeWantToPlay(Builder $query): Builder
    {
        return $query->where('backlog_status', BacklogStatus::WantToPlay->value);
    }

    public function scopePlaying(Builder $query): Builder
    {
        return $query->where('backlog_status', BacklogStatus::Playing->value);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('backlog_status', BacklogStatus::Completed->value);
    }

    public function scopeDropped(Builder $query): Builder
    {
        return $query->where('backlog_status', BacklogStatus::Dropped->value);
    }

    public function isInBacklog(): bool
    {
        return $this->backlog_status !== null;
    }
}
