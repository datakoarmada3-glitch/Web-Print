<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Printer;
use App\Models\PrintJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));

        [$year, $monthNum] = explode('-', $month);

        $userStats = PrintJob::select('user_id', DB::raw('COUNT(*) as total_jobs'), DB::raw('SUM(COALESCE(page_count, 1)) as total_pages'))
            ->whereYear('submitted_at', $year)
            ->whereMonth('submitted_at', $monthNum)
            ->groupBy('user_id')
            ->with('user:id,name,username')
            ->get();

        $printerStats = PrintJob::select('printer_id', DB::raw('COUNT(*) as total_jobs'), DB::raw('SUM(COALESCE(page_count, 1)) as total_pages'))
            ->whereYear('submitted_at', $year)
            ->whereMonth('submitted_at', $monthNum)
            ->groupBy('printer_id')
            ->with('printer:id,name,location')
            ->get();

        $summary = [
            'total_jobs' => PrintJob::whereYear('submitted_at', $year)->whereMonth('submitted_at', $monthNum)->count(),
            'total_pages' => PrintJob::whereYear('submitted_at', $year)->whereMonth('submitted_at', $monthNum)->sum('page_count') ?? 0,
            'completed' => PrintJob::whereYear('submitted_at', $year)->whereMonth('submitted_at', $monthNum)->where('status', 'completed')->count(),
            'failed' => PrintJob::whereYear('submitted_at', $year)->whereMonth('submitted_at', $monthNum)->where('status', 'failed')->count(),
        ];

        return view('admin.reports.index', compact('userStats', 'printerStats', 'summary', 'month'));
    }
}
