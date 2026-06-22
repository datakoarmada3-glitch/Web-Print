<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PrintJobStatus;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessPrintJob;
use App\Models\PrintJob;
use App\Models\Setting;
use App\Services\CupsService;
use App\Services\PrintJobService;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function index()
    {
        $activeJobs = PrintJob::with(['user', 'printer'])
            ->whereIn('status', [
                PrintJobStatus::Waiting->value,
                PrintJobStatus::Processing->value,
                PrintJobStatus::Printing->value,
            ])
            ->orderBy('submitted_at')
            ->paginate(20);

        $isPaused = Setting::getValue('queue_paused', false);

        return view('admin.queue.index', compact('activeJobs', 'isPaused'));
    }

    public function cancel(PrintJob $printJob, PrintJobService $printJobService)
    {
        try {
            $printJobService->cancelJob($printJob);
            return back()->with('success', "Job {$printJob->job_code} berhasil dibatalkan.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membatalkan job: ' . $e->getMessage());
        }
    }

    public function retry(PrintJob $printJob, PrintJobService $printJobService)
    {
        if ($printJob->status !== PrintJobStatus::Failed) {
            return back()->with('error', 'Hanya job gagal yang bisa di-retry.');
        }

        $printJob->update([
            'status' => PrintJobStatus::Waiting,
            'error_message' => null,
            'cups_job_id' => null,
        ]);

        $printJobService->log($printJob, PrintJobStatus::Waiting->value, 'Job retried by admin.');
        ProcessPrintJob::dispatch($printJob)->onQueue('prints');

        return back()->with('success', "Job {$printJob->job_code} di-retry.");
    }

    public function pause(CupsService $cupsService)
    {
        Setting::setValue('queue_paused', 'true');
        return back()->with('success', 'Antrean print dijeda.');
    }

    public function resume(CupsService $cupsService)
    {
        Setting::setValue('queue_paused', 'false');
        return back()->with('success', 'Antrean print dilanjutkan.');
    }
}
