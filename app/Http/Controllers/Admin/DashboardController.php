<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StatisticsService;

class DashboardController extends Controller
{
    public function index(StatisticsService $statsService)
    {
        $todayStats = $statsService->getTodayStats();
        $monthStats = $statsService->getMonthStats();
        $statusCounts = $statsService->getStatusCounts();
        $topUsers = $statsService->getTopUsers();
        $dailyChart = $statsService->getDailyChart();
        $monthlyChart = $statsService->getMonthlyChart();

        return view('admin.dashboard', compact(
            'todayStats', 'monthStats', 'statusCounts', 'topUsers', 'dailyChart', 'monthlyChart'
        ));
    }
}
