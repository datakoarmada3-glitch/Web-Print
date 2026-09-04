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
        if (! $job->printer) {
            $printJobService->log($job, PrintJobStatus::Printing->value, 'CUPS verify pending: printer relation missing.', ['cups_job_id' => $job->cups_job_id]);
            return;
        }
        $cupsName = $job->printer->cups_name;
        $notCompleted = $this->runLpstat(['lpstat', '-W', 'not-completed', '-o', $cupsName]);
        if ($notCompleted === null) {
            $printJobService->log($job, PrintJobStatus::Printing->value, 'Failed to poll CUPS status.', ['error' => 'lpstat not-completed failed']);
            $this->error("Failed to poll status for {$job->job_code}");
            return;
        }
        $jobNumber = $this->jobNumber($job->cups_job_id);
        if (str_contains($notCompleted, $job->cups_job_id) || ($jobNumber && str_contains($notCompleted, $jobNumber))) {
            return;
        }
        $completed = $this->runLpstat(['lpstat', '-W', 'completed', '-o', $cupsName]);
        if ($completed === null) {
            $printJobService->log($job, PrintJobStatus::Printing->value, 'CUPS verify pending: completed history unavailable.', ['cups_job_id' => $job->cups_job_id]);
            return;
        }
        if (str_contains($completed, $job->cups_job_id) || ($jobNumber && str_contains($completed, $jobNumber))) {
            $job->update(['status' => PrintJobStatus::Completed, 'completed_at' => now()]);
            $printJobService->log($job, PrintJobStatus::Completed->value, 'Print job completed successfully.', ['cups_job_id' => $job->cups_job_id]);
            $this->line("Job {$job->job_code} completed.");
            return;
        }
        $printJobService->log($job, PrintJobStatus::Printing->value, 'CUPS verify pending: job not in completed history yet.', ['cups_job_id' => $job->cups_job_id]);
    }

    private function runLpstat(array $args): ?string
    {
        $process = new Process($args);
        $process->setTimeout(15);
        $cupsServer = env('CUPS_SERVER');
        if ($cupsServer) { $process->setEnv(['CUPS_SERVER' => $cupsServer]); }
        $process->run();
        if (! $process->isSuccessful()) { return null; }
        return $process->getOutput();
    }

    private function jobNumber(string $cupsJobId): ?string
    {
        $parts = explode('-', $cupsJobId);
        $n = end($parts);
        return $n !== false && $n !== '' ? $n : null;
    }
}
