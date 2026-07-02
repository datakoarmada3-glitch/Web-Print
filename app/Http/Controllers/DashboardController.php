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

        return view('dashboard', compact('recentJobs', 'stats'));
    }
}
