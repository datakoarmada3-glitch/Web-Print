<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Printer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cups_name',
        'driver',
        'connection_uri',
        'ip_address',
        'location',
        'status',
        'capabilities',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'capabilities' => 'array',
            'is_default' => 'boolean',
        ];
    }

    public function printJobs(): HasMany
    {
        return $this->hasMany(PrintJob::class);
    }

    public function isOnline(): bool
    {
        return in_array($this->status, ['online', 'idle', 'printing']);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'online', 'idle' => 'bg-success',
            'printing' => 'bg-primary',
            'error' => 'bg-danger',
            'offline' => 'bg-secondary',
            'paused' => 'bg-warning',
            default => 'bg-muted',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'online' => 'Online',
            'idle' => 'Idle',
            'printing' => 'Mencetak',
            'error' => 'Error',
            'offline' => 'Offline',
            'paused' => 'Dijeda',
            default => 'Tidak Diketahui',
        };
    }
}
