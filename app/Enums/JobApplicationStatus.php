<?php

namespace App\Enums;

enum JobApplicationStatus: string
{
    case Saved = 'saved';
    case Applied = 'applied';
    case Interviewing = 'interviewing';
    case Offered = 'offered';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Saved => 'Saved',
            self::Applied => 'Applied',
            self::Interviewing => 'Interviewing',
            self::Offered => 'Offered',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Saved => '#6b7280',
            self::Applied => '#3b82f6',
            self::Interviewing => '#f59e0b',
            self::Offered => '#10b981',
            self::Rejected => '#ef4444',
        };
    }
}
