<?php

namespace App\Enums;

enum EmailSender: string
{
    case Scammer = 'scammer';
    case Marcus = 'marcus';

    public function label(): string
    {
        return match ($this) {
            self::Scammer => 'Scammer',
            self::Marcus => 'Marcus',
        };
    }
}
