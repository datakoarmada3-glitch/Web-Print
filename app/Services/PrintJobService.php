<?php

namespace App\Services;

use App\Enums\PrintJobStatus;
use App\Jobs\ProcessPrintJob;
use App\Models\Printer;
use App\Models\PrintJob;
use App\Models\PrintJobLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PrintJobService
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {}

    public function createJob(int $userId, UploadedFile $file, array $options): PrintJob
    {
        $upload = $this->fileUploadService->storeUploadedFile($file);
        $printer = Printer::where('is_default', true)->firstOrFail();

        return DB::transaction(function () use ($userId, $upload, $options, $printer) {
            $printJob = PrintJob::create([
                'job_code' => PrintJob::generateJobCode(),
                'user_id' => $userId,
                'printer_id' => $printer->id,
                'original_filename' => $upload['original_filename'],
                'stored_original_path' => $upload['stored_original_path'],
                'file_type' => $upload['file_type'],
                'file_size' => $upload['file_size'],
                'copies' => $options['copies'],
                'paper_size' => $options['paper_size'],
                'orientation' => $options['orientation'],
                'duplex' => $options['duplex'],
                'color_mode' => $options['color_mode'],
                'page_range' => $options['page_range'] ?? null,
                'status' => PrintJobStatus::Waiting,
                'submitted_at' => now(),
            ]);

            $this->log($printJob, PrintJobStatus::Waiting->value, 'Print job submitted.');
            ProcessPrintJob::dispatch($printJob)->onQueue('prints');

            return $printJob;
        });
    }

    public function cancelJob(PrintJob $printJob): void
    {
        if (!$printJob->isCancellable()) {
            throw new \RuntimeException('Print job cannot be cancelled.');
        }

        $printJob->update([
            'status' => PrintJobStatus::Cancelled,
            'cancelled_at' => now(),
        ]);

        $this->log($printJob, PrintJobStatus::Cancelled->value, 'Print job cancelled.');
    }

    public function log(PrintJob $printJob, string $status, ?string $message = null, ?array $context = null): void
    {
        PrintJobLog::create([
            'print_job_id' => $printJob->id,
            'status' => $status,
            'message' => $message,
            'context' => $context,
        ]);
    }
}
