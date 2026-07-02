<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrintJob;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = PrintJob::with(['user', 'printer'])->latest('submitted_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('user')) {
            $query->where('user_id', $request->input('user'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('job_code', 'like', "%{$search}%")
                    ->orWhere('original_filename', 'like', "%{$search}%");
            });
        }

        $printJobs = $query->paginate(20)->appends($request->query());

        return view('admin.history.index', compact('printJobs'));
    }

    public function show(PrintJob $printJob)
    {
        $printJob->load(['user', 'printer', 'logs']);

        return view('admin.history.show', compact('printJob'));
    }

    public function statuses()
    {
        $jobs = PrintJob::with('printer')
            ->latest('submitted_at')
            ->take(20)
            ->get();

        return response()->json($jobs->map(fn (PrintJob $printJob) => [
            'id' => $printJob->id,
            'status' => $printJob->status->value,
            'label' => $printJob->status->label(),
            'badge' => $this->badgeClass($printJob->status->value),
            'isTerminal' => $printJob->status->isTerminal(),
            'printerName' => $printJob->printer?->name,
        ]));
    }

    private function badgeClass(string $status): string
    {
        return match ($status) {
            'previewing', 'processing', 'printing' => 'info',
            'ready' => 'primary',
            'waiting' => 'warning',
            'completed' => 'success',
            'failed' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }
}
