<?php

namespace App\Enums;

enum PaperSize: string
{
    case A4 = 'A4';
    case Legal = 'Legal';
    case F4 = 'F4';

    public function cupsMedia(): string
    {
        return match ($this) {
            self::A4 => 'A4',
            self::Legal => 'Legal',
            self::F4 => 'F4A', // Canon iR2625 UFR II uses F4A for Folio/F4
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::A4 => 'A4 (210 x 297 mm)',
            self::Legal => 'Legal (216 x 356 mm)',
            self::F4 => 'F4/Folio (215 x 330 mm)',
        };
    }
}
