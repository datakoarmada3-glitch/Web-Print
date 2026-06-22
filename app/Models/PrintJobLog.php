<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintJobLog extends Model
{
    protected $fillable = [
        'print_job_id',
        'status',
        'message',
        'context',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
        ];
    }

    public function printJob(): BelongsTo
    {
        return $this->belongsTo(PrintJob::class);
    }
}
