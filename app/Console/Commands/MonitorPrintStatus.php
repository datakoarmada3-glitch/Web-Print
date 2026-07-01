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
        $process = new Process(['lpstat', '-W', 'not-completed', '-o', $cupsName]);
        $process->setTimeout(15);

        $cupsServer = env('CUPS_SERVER');
        if ($cupsServer) {
            $process->setEnv(['CUPS_SERVER' => $cupsServer]);
        }

        $process->run();

        if (!$process->isSuccessful()) {
            $errorOutput = trim($process->getErrorOutput()) ?: 'lpstat status check failed.';
            $printJobService->log(
                $job,
                PrintJobStatus::Printing->value,
                'Failed to poll CUPS status.',
                ['error' => $errorOutput]
            );
            $this->error("Failed to poll status for {$job->job_code}: {$errorOutput}");

            return;
        }

        $notCompleted = $process->getOutput();
        $jobIdParts = explode('-', $job->cups_job_id);
        $jobNumber = end($jobIdParts);

        if (str_contains($notCompleted, $job->cups_job_id) || str_contains($notCompleted, $jobNumber)) {
            return;
        }

        $job->update([
            'status' => PrintJobStatus::Completed,
            'completed_at' => now(),
        ]);

        $printJobService->log($job, PrintJobStatus::Completed->value, 'Print job completed successfully.');
        $this->line("Job {$job->job_code} completed.");
    }
}
