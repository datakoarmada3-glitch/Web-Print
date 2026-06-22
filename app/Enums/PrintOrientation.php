<?php

namespace App\Enums;

enum PrintOrientation: string
{
    case Portrait = 'portrait';
    case Landscape = 'landscape';

    public function cupsCode(): string
    {
        return match ($this) {
            self::Portrait => '3',
            self::Landscape => '4',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Portrait => 'Portrait',
            self::Landscape => 'Landscape',
        };
    }
}
