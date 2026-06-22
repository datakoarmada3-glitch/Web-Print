<?php

namespace App\Services;

use App\Enums\PrintJobStatus;
use App\Models\PrintJob;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    public function getTodayStats(): array
    {
        $today = today();

        return [
            'jobs_today' => PrintJob::whereDate('submitted_at', $today)->count(),
            'pages_today' => PrintJob::whereDate('submitted_at', $today)->sum('page_count') ?? 0,
            'sheets_today' => $this->estimatedSheetsToday(),
        ];
    }

    public function getMonthStats(): array
    {
        $startOfMonth = now()->startOfMonth();

        return [
            'jobs_month' => PrintJob::where('submitted_at', '>=', $startOfMonth)->count(),
            'pages_month' => PrintJob::where('submitted_at', '>=', $startOfMonth)->sum('page_count') ?? 0,
            'sheets_month' => $this->estimatedSheetsMonth(),
        ];
    }

    public function getStatusCounts(): array
    {
        return [
            'success' => PrintJob::where('status', PrintJobStatus::Completed->value)->count(),
            'failed' => PrintJob::where('status', PrintJobStatus::Failed->value)->count(),
            'cancelled' => PrintJob::where('status', PrintJobStatus::Cancelled->value)->count(),
            'pending' => PrintJob::whereIn('status', [
                PrintJobStatus::Waiting->value,
                PrintJobStatus::Processing->value,
                PrintJobStatus::Printing->value,
            ])->count(),
        ];
    }

    public function getTopUsers(int $limit = 5): array
    {
        return PrintJob::select('user_id', DB::raw('COUNT(*) as total_jobs'))
            ->where('submitted_at', '>=', now()->startOfMonth())
            ->groupBy('user_id')
            ->orderByDesc('total_jobs')
            ->limit($limit)
            ->with('user:id,name')
            ->get()
            ->map(fn ($item) => [
                'name' => $item->user->name ?? 'Unknown',
                'total_jobs' => $item->total_jobs,
            ])
            ->toArray();
    }

    public function getDailyChart(int $days = 30): array
    {
        $startDate = now()->subDays($days)->startOfDay();

        $data = PrintJob::select(
            DB::raw('DATE(submitted_at) as date'),
            DB::raw('COUNT(*) as total_jobs'),
            DB::raw('SUM(page_count) as total_pages')
        )
            ->where('submitted_at', '>=', $startDate)
            ->groupBy(DB::raw('DATE(submitted_at)'))
            ->orderBy('date')
            ->get();

        $labels = [];
        $jobs = [];
        $pages = [];

        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('d/m');
            $row = $data->firstWhere('date', $date);
            $jobs[] = $row ? $row->total_jobs : 0;
            $pages[] = $row ? ($row->total_pages ?? 0) : 0;
        }

        return compact('labels', 'jobs', 'pages');
    }

    public function getMonthlyChart(int $months = 12): array
    {
        $startDate = now()->subMonths($months)->startOfMonth();

        $data = PrintJob::select(
            DB::raw('YEAR(submitted_at) as year'),
            DB::raw('MONTH(submitted_at) as month'),
            DB::raw('COUNT(*) as total_jobs'),
            DB::raw('SUM(page_count) as total_pages')
        )
            ->where('submitted_at', '>=', $startDate)
            ->groupBy(DB::raw('YEAR(submitted_at)'), DB::raw('MONTH(submitted_at)'))
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $labels = [];
        $jobs = [];
        $pages = [];

        for ($i = $months; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M Y');
            $row = $data->first(fn ($r) => $r->year == $date->year && $r->month == $date->month);
            $jobs[] = $row ? $row->total_jobs : 0;
            $pages[] = $row ? ($row->total_pages ?? 0) : 0;
        }

        return compact('labels', 'jobs', 'pages');
    }

    private function estimatedSheetsToday(): int
    {
        return PrintJob::whereDate('submitted_at', today())
            ->where('status', PrintJobStatus::Completed->value)
            ->get()
            ->sum(fn ($job) => $job->estimatedSheets());
    }

    private function estimatedSheetsMonth(): int
    {
        return PrintJob::where('submitted_at', '>=', now()->startOfMonth())
            ->where('status', PrintJobStatus::Completed->value)
            ->get()
            ->sum(fn ($job) => $job->estimatedSheets());
    }
}
