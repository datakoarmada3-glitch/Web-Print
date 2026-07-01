<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePrintJobRequest;
use App\Models\PrintJob;
use App\Services\PrintJobService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function preview(Request $request, PrintJob $printJob)
    {
        abort_unless($printJob->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        $pdfPath = $printJob->getPdfPath();
        abort_unless($pdfPath && Storage::disk('local')->exists($pdfPath), 404, 'File PDF belum tersedia.');

        return response()->file(Storage::disk('local')->path($pdfPath), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $printJob->job_code . '.pdf"',
        ]);
    }

    public function download(Request $request, PrintJob $printJob)
    {
        abort_unless($printJob->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        $pdfPath = $printJob->getPdfPath();
        abort_unless($pdfPath && Storage::disk('local')->exists($pdfPath), 404, 'File PDF belum tersedia.');

        return Storage::disk('local')->download($pdfPath, $printJob->job_code . '.pdf');
    }
}
