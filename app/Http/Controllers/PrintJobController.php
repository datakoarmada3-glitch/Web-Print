<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePrintJobRequest;
use App\Models\PrintJob;
use App\Services\PrintJobService;
use Illuminate\Http\Request;

class PrintJobController extends Controller
{
    public function __construct(
        private readonly PrintJobService $printJobService,
    ) {}

    public function index(Request $request)
    {
        $printJobs = PrintJob::with('printer')
            ->where('user_id', $request->user()->id)
            ->latest('submitted_at')
            ->paginate(15);

        return view('print-jobs.index', compact('printJobs'));
    }

    public function create()
    {
        return view('print-jobs.create');
    }

    public function store(StorePrintJobRequest $request)
    {
        $printJob = $this->printJobService->createJob(
            $request->user()->id,
            $request->file('document'),
            $request->validated(),
        );

        return redirect()
            ->route('print-jobs.show', $printJob)
            ->with('success', 'Dokumen berhasil diunggah dan masuk antrean print.');
    }

    public function show(Request $request, PrintJob $printJob)
    {
        abort_unless($printJob->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        $printJob->load(['printer', 'logs']);

        return view('print-jobs.show', compact('printJob'));
    }

    public function cancel(Request $request, PrintJob $printJob)
    {
        abort_unless($printJob->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        try {
            $this->printJobService->cancelJob($printJob);

            return redirect()
                ->route('print-jobs.show', $printJob)
                ->with('success', 'Print job berhasil dibatalkan.');
        } catch (\Throwable $exception) {
            return back()->with('error', 'Gagal membatalkan print job: ' . $exception->getMessage());
        }
    }
}
