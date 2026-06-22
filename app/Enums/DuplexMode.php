<?php

namespace App\Enums;

enum DuplexMode: string
{
    case None = 'none';
    case LongEdge = 'long-edge';
    case ShortEdge = 'short-edge';

    public function cupsSides(): string
    {
        return match ($this) {
            self::None => 'one-sided',
            self::LongEdge => 'two-sided-long-edge',
            self::ShortEdge => 'two-sided-short-edge',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::None => 'Satu Sisi',
            self::LongEdge => 'Dua Sisi (Tepi Panjang)',
            self::ShortEdge => 'Dua Sisi (Tepi Pendek)',
        };
    }
}
