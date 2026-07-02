<?php

namespace App\Jobs;

use App\Enums\PrintJobStatus;
use App\Models\PrintJob;
use App\Services\CupsService;
use App\Services\FileConversionService;
use App\Services\FileUploadService;
use App\Services\PrintJobService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessPrintJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 180;

    public function __construct(
        public readonly PrintJob $printJob,
    ) {
        $this->onQueue('prints');
    }

    public function handle(
        CupsService $cupsService,
        FileUploadService $fileUploadService,
        FileConversionService $conversionService,
        PrintJobService $printJobService,
    ): void {
        $this->printJob->refresh();

        if ($this->printJob->status === PrintJobStatus::Cancelled) {
            return;
        }

        $this->printJob->update([
            'status' => PrintJobStatus::Processing,
            'processing_started_at' => now(),
        ]);

        $printJobService->log(
            $this->printJob,
            PrintJobStatus::Processing->value,
            'Print job processing started.'
        );

        try {
            // Convert to PDF if needed and no preview PDF exists yet.
            if (!$this->printJob->converted_pdf_path && $conversionService->needsConversion($this->printJob->file_type)) {
                $convertedPath = $conversionService->convertToPdf(
                    $this->printJob->stored_original_path,
                    $this->printJob->file_type
                );

                $this->printJob->update(['converted_pdf_path' => $convertedPath]);

                $printJobService->log(
                    $this->printJob,
                    PrintJobStatus::Processing->value,
                    'File converted to PDF.'
                );
            }

            // Count pages
            $pdfPath = $this->printJob->getPdfPath();
            $pageCount = $this->printJob->page_count ?: $conversionService->getPageCount($pdfPath);
            if ($pageCount) {
                $this->printJob->update(['page_count' => $pageCount]);
            }

            // Validate source exists
            if (!Storage::disk('local')->exists($pdfPath)) {
                throw new \RuntimeException('Source print file not found.');
            }

            $absolutePath = $fileUploadService->absolutePath($pdfPath);
            $cupsJobId = $cupsService->sendPrintJob($this->printJob, $absolutePath);

            $this->printJob->update([
                'status' => PrintJobStatus::Printing,
                'cups_job_id' => $cupsJobId,
                'printed_at' => now(),
            ]);

            $printJobService->log(
                $this->printJob,
                PrintJobStatus::Printing->value,
                'Print job sent to CUPS.',
                ['cups_job_id' => $cupsJobId]
            );
        } catch (Throwable $exception) {
            $this->printJob->update([
                'status' => PrintJobStatus::Failed,
                'error_message' => $exception->getMessage(),
            ]);

            $printJobService->log(
                $this->printJob,
                PrintJobStatus::Failed->value,
                'Print job failed.',
                ['error' => $exception->getMessage()]
            );

            throw $exception;
        }
    }
}
