<?php

namespace App\Enums;

enum PrintJobStatus: string
{
    case Previewing = 'previewing';
    case Ready = 'ready';
    case Waiting = 'waiting';
    case Processing = 'processing';
    case Printing = 'printing';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Previewing => 'Membuat Preview',
            self::Ready => 'Siap Print',
            self::Waiting => 'Menunggu',
            self::Processing => 'Memproses',
            self::Printing => 'Mencetak',
            self::Completed => 'Selesai',
            self::Failed => 'Gagal',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Previewing => 'bg-info',
            self::Ready => 'bg-primary',
            self::Waiting => 'bg-warning',
            self::Processing => 'bg-info',
            self::Printing => 'bg-primary',
            self::Completed => 'bg-success',
            self::Failed => 'bg-danger',
            self::Cancelled => 'bg-secondary',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Failed, self::Cancelled]);
    }

    public function isCancellable(): bool
    {
        return in_array($this, [self::Previewing, self::Ready, self::Waiting, self::Processing]);
    }
}
