<?php

namespace App\Enums;

enum BacklogStatus: string
{
    case WantToPlay = 'want_to_play';
    case Playing = 'playing';
    case Completed = 'completed';
    case Dropped = 'dropped';

    public function label(): string
    {
        return match ($this) {
            self::WantToPlay => 'Wil spelen',
            self::Playing => 'Nu aan het spelen',
            self::Completed => 'Uitgespeeld',
            self::Dropped => 'Gestopt',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::WantToPlay => '#3b82f6',
            self::Playing => '#10b981',
            self::Completed => '#8b5cf6',
            self::Dropped => '#6b7280',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::WantToPlay => 'fas fa-bookmark',
            self::Playing => 'fas fa-play',
            self::Completed => 'fas fa-check-circle',
            self::Dropped => 'fas fa-times-circle',
        };
    }
}
