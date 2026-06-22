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
            self::F4 => 'Legal', // F4 mapped to Legal in CUPS
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
