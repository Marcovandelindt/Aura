<?php

namespace App\Enums;

enum PlayMode: string
{
    case Singleplayer = 'singleplayer';
    case Multiplayer = 'multiplayer';
    case Both = 'both';

    public function label(): string
    {
        return match ($this) {
            self::Singleplayer => 'Singleplayer',
            self::Multiplayer => 'Multiplayer',
            self::Both => 'Single & Multiplayer',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Singleplayer => 'fas fa-user',
            self::Multiplayer => 'fas fa-users',
            self::Both => 'fas fa-user-friends',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Singleplayer => '#8b5cf6',
            self::Multiplayer => '#f59e0b',
            self::Both => '#10b981',
        };
    }
}
