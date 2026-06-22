<?php

namespace App\Console\Commands;

use App\Enums\PrintJobStatus;
use App\Models\PrintJob;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupOldPrintFiles extends Command
{
    protected $signature = 'print:cleanup-files';
    protected $description = 'Delete original/converted files older than retention period';

    public function handle(): int
    {
        $retentionDays = (int) Setting::getValue('file_retention_days', config('print.file_retention_days', 30));
        $cutoff = now()->subDays($retentionDays);

        $jobs = PrintJob::where('created_at', '<', $cutoff)
            ->whereIn('status', [
                PrintJobStatus::Completed->value,
                PrintJobStatus::Failed->value,
                PrintJobStatus::Cancelled->value,
            ])
            ->whereNotNull('stored_original_path')
            ->cursor();

        $count = 0;

        foreach ($jobs as $job) {
            if ($job->stored_original_path && Storage::disk('local')->exists($job->stored_original_path)) {
                Storage::disk('local')->delete($job->stored_original_path);
            }

            if ($job->converted_pdf_path && Storage::disk('local')->exists($job->converted_pdf_path)) {
                Storage::disk('local')->delete($job->converted_pdf_path);
            }

            $job->update([
                'stored_original_path' => null,
                'converted_pdf_path' => null,
            ]);

            $count++;
        }

        $this->info("Cleaned up files for {$count} print jobs (older than {$retentionDays} days).");

        return self::SUCCESS;
    }
}
