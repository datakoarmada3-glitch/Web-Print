<?php

namespace App\Console\Commands;

use App\Enums\PrintJobStatus;
use App\Models\PrintJob;
use App\Services\PrintJobService;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class MonitorPrintStatus extends Command
{
    protected $signature = 'print:monitor-status';
    protected $description = 'Poll CUPS for active print job statuses and update database';

    public function handle(PrintJobService $printJobService): int
    {
        $activeJobs = PrintJob::with('printer')
            ->where('status', PrintJobStatus::Printing->value)
            ->whereNotNull('cups_job_id')
            ->get();

        if ($activeJobs->isEmpty()) {
            return self::SUCCESS;
        }

        foreach ($activeJobs as $job) {
            $this->checkJobStatus($job, $printJobService);
        }

        return self::SUCCESS;
    }

    private function checkJobStatus(PrintJob $job, PrintJobService $printJobService): void
    {
        $cupsName = $job->printer->cups_name;

        // Check if job is still in not-completed queue
        $process = new Process(['lpstat', '-W', 'not-completed', '-o', $cupsName]);
        $process->setTimeout(15);
        $process->run();

        $notCompleted = $process->getOutput();
        $jobIdParts = explode('-', $job->cups_job_id);
        $jobNumber = end($jobIdParts);

        // If job is NOT in the not-completed list, it's done
        if (!str_contains($notCompleted, $job->cups_job_id) && !str_contains($notCompleted, $jobNumber)) {
            $job->update([
                'status' => PrintJobStatus::Completed,
                'completed_at' => now(),
            ]);

            $printJobService->log($job, PrintJobStatus::Completed->value, 'Print job completed successfully.');
            $this->line("Job {$job->job_code} completed.");
        }
    }
}
