<?php

namespace App\Services;

use App\Enums\PrintJobStatus;
use App\Jobs\ProcessPrintJob;
use App\Models\Printer;
use App\Models\PrintJob;
use App\Models\PrintJobLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class PrintJobService
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
        private readonly FileConversionService $conversionService,
    ) {}

    public function createPreviewJob(int $userId, UploadedFile $file, array $options): PrintJob
    {
        $upload = $this->fileUploadService->storeUploadedFile($file);
        $printer = $this->resolvePrinter($options['printer_id'] ?? null);
        $printJob = $this->createStoredJob($userId, $upload, $options, $printer, PrintJobStatus::Previewing);

        try {
            return $this->preparePreview($printJob);
        } catch (Throwable $exception) {
            $printJob->update([
                'status' => PrintJobStatus::Failed,
                'error_message' => $exception->getMessage(),
            ]);
            $this->log($printJob, PrintJobStatus::Failed->value, 'Gagal membuat preview PDF.', [
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function createJob(int $userId, UploadedFile $file, array $options): PrintJob
    {
        $upload = $this->fileUploadService->storeUploadedFile($file);
        $printer = $this->resolvePrinter($options['printer_id'] ?? null);
        $printJob = $this->createStoredJob($userId, $upload, $options, $printer, PrintJobStatus::Waiting);

        ProcessPrintJob::dispatch($printJob)->onQueue('prints');

        return $printJob;
    }

    public function confirmJob(PrintJob $printJob, ?string $pageRange = null): void
    {
        if ($printJob->status !== PrintJobStatus::Ready) {
            throw new \RuntimeException('Print job belum siap dikirim ke printer.');
        }

        $printJob->update([
            'page_range' => $this->normalizePageRange($pageRange),
            'status' => PrintJobStatus::Waiting,
            'error_message' => null,
            'submitted_at' => now(),
        ]);

        $this->log($printJob, PrintJobStatus::Waiting->value, 'Print job confirmed and queued.');
        ProcessPrintJob::dispatch($printJob)->onQueue('prints');
    }

    public function cancelJob(PrintJob $printJob): void
    {
        if (!$printJob->isCancellable()) {
            throw new \RuntimeException('Print job tidak bisa dibatalkan.');
        }

        $printJob->update([
            'status' => PrintJobStatus::Cancelled,
            'cancelled_at' => now(),
        ]);

        $this->log($printJob, PrintJobStatus::Cancelled->value, 'Print job cancelled.');
    }

    /**
     * @param array<string, mixed> $upload
     * @param array<string, mixed> $options
     */
    private function createStoredJob(
        int $userId,
        array $upload,
        array $options,
        Printer $printer,
        PrintJobStatus $status,
    ): PrintJob {
        return DB::transaction(function () use ($userId, $upload, $options, $printer, $status) {
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
                'status' => $status,
                'submitted_at' => now(),
            ]);

            $this->log($printJob, $status->value, $status === PrintJobStatus::Previewing
                ? 'Print job uploaded for PDF preview.'
                : 'Print job submitted.');

            return $printJob;
        });
    }

    private function preparePreview(PrintJob $printJob): PrintJob
    {
        $pdfPath = $printJob->stored_original_path;

        if ($this->conversionService->needsConversion($printJob->file_type)) {
            $pdfPath = $this->conversionService->convertToPdf($printJob->stored_original_path, $printJob->file_type);
            $printJob->update(['converted_pdf_path' => $pdfPath]);
            $this->log($printJob, PrintJobStatus::Previewing->value, 'File converted to PDF preview.');
        }

        $pageCount = $this->conversionService->getPageCount($pdfPath);
        $printJob->update([
            'page_count' => $pageCount,
            'status' => PrintJobStatus::Ready,
        ]);

        $this->log($printJob, PrintJobStatus::Ready->value, 'Preview PDF siap dikonfirmasi.');

        return $printJob->fresh(['printer', 'logs']);
    }

    private function normalizePageRange(?string $pageRange): ?string
    {
        $normalized = preg_replace('/\s+/', '', $pageRange ?? '');

        return $normalized === '' ? null : $normalized;
    }

    private function resolvePrinter(mixed $printerId): Printer
    {
        if ($printerId) {
            return Printer::findOrFail($printerId);
        }

        return Printer::where('is_default', true)->firstOrFail();
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
