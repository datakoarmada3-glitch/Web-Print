<?php

namespace App\Models;

use App\Enums\ColorMode;
use App\Enums\DuplexMode;
use App\Enums\PaperSize;
use App\Enums\PrintJobStatus;
use App\Enums\PrintOrientation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrintJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_code',
        'user_id',
        'printer_id',
        'original_filename',
        'stored_original_path',
        'converted_pdf_path',
        'file_type',
        'file_size',
        'page_count',
        'copies',
        'paper_size',
        'orientation',
        'duplex',
        'color_mode',
        'page_range',
        'status',
        'cups_job_id',
        'error_message',
        'submitted_at',
        'processing_started_at',
        'printed_at',
        'completed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PrintJobStatus::class,
            'paper_size' => PaperSize::class,
            'orientation' => PrintOrientation::class,
            'duplex' => DuplexMode::class,
            'color_mode' => ColorMode::class,
            'file_size' => 'integer',
            'page_count' => 'integer',
            'copies' => 'integer',
            'submitted_at' => 'datetime',
            'processing_started_at' => 'datetime',
            'printed_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function printer(): BelongsTo
    {
        return $this->belongsTo(Printer::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PrintJobLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Generate next job code: PRN-YYYYMMDD-NNNN
     */
    public static function generateJobCode(): string
    {
        $today = now()->format('Ymd');
        $prefix = "PRN-{$today}-";

        $lastJob = static::where('job_code', 'like', "{$prefix}%")
            ->orderBy('job_code', 'desc')
            ->first();

        if ($lastJob) {
            $lastNumber = (int) substr($lastJob->job_code, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Estimate total sheets used
     */
    public function estimatedSheets(): int
    {
        if (!$this->page_count) {
            return $this->copies;
        }

        $pages = $this->page_count;

        // Apply page range if set
        if ($this->page_range) {
            $pages = $this->countPagesFromRange($this->page_range, $pages);
        }

        // Duplex halves the sheets
        if ($this->duplex !== DuplexMode::None) {
            $sheets = (int) ceil($pages / 2);
        } else {
            $sheets = $pages;
        }

        return $sheets * $this->copies;
    }

    /**
     * Count pages from a range string like "1-5" or "1,3,5-10"
     */
    private function countPagesFromRange(string $range, int $totalPages): int
    {
        $count = 0;
        $parts = explode(',', $range);

        foreach ($parts as $part) {
            $part = trim($part);
            if (str_contains($part, '-')) {
                [$start, $end] = explode('-', $part, 2);
                $start = max(1, (int) $start);
                $end = min($totalPages, (int) $end);
                if ($end >= $start) {
                    $count += ($end - $start + 1);
                }
            } else {
                $page = (int) $part;
                if ($page >= 1 && $page <= $totalPages) {
                    $count++;
                }
            }
        }

        return $count ?: $totalPages;
    }

    public function fileSizeFormatted(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }
        return number_format($bytes / 1024, 0) . ' KB';
    }

    public function isCancellable(): bool
    {
        return $this->status->isCancellable();
    }

    public function getPdfPath(): string
    {
        return $this->converted_pdf_path ?? $this->stored_original_path;
    }
}
