<?php

namespace App\Enums;

enum IntelligenceLevel: string
{
    case VeryDim = 'very_dim';
    case Dim = 'dim';
    case Average = 'average';
    case Sharp = 'sharp';
    case SurprisinglySharp = 'surprisingly_sharp';

    public function label(): string
    {
        return match ($this) {
            self::VeryDim => 'Very Dim',
            self::Dim => 'Dim',
            self::Average => 'Average',
            self::Sharp => 'Sharp',
            self::SurprisinglySharp => 'Surprisingly Sharp',
        };
    }
}
