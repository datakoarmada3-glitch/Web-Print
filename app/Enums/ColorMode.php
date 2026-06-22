<?php

namespace App\Enums;

enum ColorMode: string
{
    case Color = 'color';
    case Grayscale = 'grayscale';

    public function cupsColorModel(): string
    {
        return match ($this) {
            self::Color => 'RGB',
            self::Grayscale => 'Gray',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Color => 'Berwarna',
            self::Grayscale => 'Hitam Putih',
        };
    }
}
