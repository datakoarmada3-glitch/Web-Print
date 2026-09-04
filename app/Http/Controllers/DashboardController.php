<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $recentJobs = PrintJob::with('printer')
            ->where('user_id', $user->id)
            ->latest('submitted_at')
            ->take(5)
            ->get();

        $stats = [
            'total_today' => PrintJob::where('user_id', $user->id)
                ->whereDate('submitted_at', today())
                ->count(),
            'total_month' => PrintJob::where('user_id', $user->id)
                ->whereMonth('submitted_at', now()->month)
                ->whereYear('submitted_at', now()->year)
                ->count(),
            'pending' => PrintJob::where('user_id', $user->id)
                ->whereIn('status', ['waiting', 'processing', 'printing'])
                ->count(),
        ];

        $stats['queue_ahead'] = PrintJob::whereIn('status', ['waiting', 'processing', 'printing'])
            ->where('submitted_at', '<', now())
            ->count();
        // Conservative guidance only; actual print time depends on file size and printer condition.
        $stats['estimated_wait_minutes'] = (int) ceil($stats['queue_ahead'] * 1.5);

        return view('dashboard', compact('recentJobs', 'stats'));
    }
}
