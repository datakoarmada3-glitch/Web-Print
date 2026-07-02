<?php

namespace App\Http\Controllers;

use App\Enums\PrintJobStatus;
use App\Http\Requests\StorePrintJobRequest;
use App\Models\Printer;
use App\Models\PrintJob;
use App\Services\PrintJobService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

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
        $printers = Printer::orderByDesc('is_default')
            ->orderBy('name')
            ->get();
        $defaultPrinterId = $printers->firstWhere('is_default', true)?->id;

        return view('print-jobs.create', compact('printers', 'defaultPrinterId'));
    }

    public function store(StorePrintJobRequest $request)
    {
        try {
            $printJob = $this->printJobService->createPreviewJob(
                $request->user()->id,
                $request->file('document'),
                $request->validated(),
            );
        } catch (Throwable $exception) {
            return back()
                ->withInput()
                ->with('error', 'Gagal membuat preview dokumen: ' . $exception->getMessage());
        }

        return redirect()
            ->route('print-jobs.show', $printJob)
            ->with('success', 'Preview PDF berhasil dibuat. Periksa dokumen sebelum dikirim ke printer.');
    }

    public function show(Request $request, PrintJob $printJob)
    {
        abort_unless($printJob->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        $printJob->load(['printer', 'logs']);

        return view('print-jobs.show', compact('printJob'));
    }

    public function confirm(Request $request, PrintJob $printJob)
    {
        abort_unless($printJob->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        try {
            $this->printJobService->confirmJob($printJob);

            return redirect()
                ->route('print-jobs.show', $printJob)
                ->with('success', 'Dokumen masuk antrean print.');
        } catch (Throwable $exception) {
            return back()->with('error', 'Gagal mengirim ke printer: ' . $exception->getMessage());
        }
    }

    public function status(Request $request, PrintJob $printJob)
    {
        abort_unless($printJob->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        $printJob->refresh()->load('printer');

        return response()->json([
            'id' => $printJob->id,
            'status' => $printJob->status->value,
            'label' => $printJob->status->label(),
            'badge' => $this->badgeClass($printJob->status),
            'isTerminal' => $printJob->status->isTerminal(),
            'isReady' => $printJob->status === PrintJobStatus::Ready,
            'previewUrl' => ($printJob->converted_pdf_path || strtolower($printJob->file_type) === 'pdf')
                ? route('print-jobs.preview', $printJob)
                : null,
            'errorMessage' => $printJob->error_message,
            'printerName' => $printJob->printer?->name,
        ]);
    }

    public function statuses(Request $request)
    {
        $jobs = PrintJob::with('printer')
            ->where('user_id', $request->user()->id)
            ->latest('submitted_at')
            ->take(10)
            ->get();

        return response()->json($jobs->map(fn (PrintJob $printJob) => [
            'id' => $printJob->id,
            'status' => $printJob->status->value,
            'label' => $printJob->status->label(),
            'badge' => $this->badgeClass($printJob->status),
            'isTerminal' => $printJob->status->isTerminal(),
            'isReady' => $printJob->status === PrintJobStatus::Ready,
            'printerName' => $printJob->printer?->name,
            'previewUrl' => ($printJob->converted_pdf_path || strtolower($printJob->file_type) === 'pdf')
                ? route('print-jobs.preview', $printJob)
                : null,
        ]));
    }

    private function badgeClass(PrintJobStatus $status): string
    {
        return match ($status) {
            PrintJobStatus::Previewing => 'info',
            PrintJobStatus::Ready => 'primary',
            PrintJobStatus::Waiting => 'warning',
            PrintJobStatus::Processing, PrintJobStatus::Printing => 'info',
            PrintJobStatus::Completed => 'success',
            PrintJobStatus::Failed => 'danger',
            PrintJobStatus::Cancelled => 'secondary',
        };
    }

    public function cancel(Request $request, PrintJob $printJob)
    {
        abort_unless($printJob->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        try {
            $this->printJobService->cancelJob($printJob);

            return redirect()
                ->route('print-jobs.show', $printJob)
                ->with('success', 'Print job berhasil dibatalkan.');
        } catch (Throwable $exception) {
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
