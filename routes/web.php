<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PrintJobController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\QueueController;
use App\Http\Controllers\Admin\HistoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PrinterController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to dashboard
Route::get('/', fn () => redirect()->route('dashboard'));

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated routes
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Print Jobs
    Route::get('/print-jobs', [PrintJobController::class, 'index'])->name('print-jobs.index');
    Route::get('/print-jobs/create', [PrintJobController::class, 'create'])->name('print-jobs.create');
    Route::post('/print-jobs', [PrintJobController::class, 'store'])->name('print-jobs.store');
    Route::get('/print-jobs/{printJob}', [PrintJobController::class, 'show'])->name('print-jobs.show');
    Route::get('/print-jobs/{printJob}/preview', [PrintJobController::class, 'preview'])->name('print-jobs.preview');
    Route::get('/print-jobs/{printJob}/download', [PrintJobController::class, 'download'])->name('print-jobs.download');
    Route::post('/print-jobs/{printJob}/cancel', [PrintJobController::class, 'cancel'])->name('print-jobs.cancel');

    // Admin routes
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Queue management
        Route::get('/queue', [QueueController::class, 'index'])->name('queue.index');
        Route::post('/queue/{printJob}/cancel', [QueueController::class, 'cancel'])->name('queue.cancel');
        Route::post('/queue/{printJob}/retry', [QueueController::class, 'retry'])->name('queue.retry');
        Route::post('/queue/pause', [QueueController::class, 'pause'])->name('queue.pause');
        Route::post('/queue/resume', [QueueController::class, 'resume'])->name('queue.resume');

        // History
        Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
        Route::get('/history/{printJob}', [HistoryController::class, 'show'])->name('history.show');

        // Users
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

        // Printers
        Route::get('/printers', [PrinterController::class, 'index'])->name('printers.index');
        Route::get('/printers/create', [PrinterController::class, 'create'])->name('printers.create');
        Route::post('/printers', [PrinterController::class, 'store'])->name('printers.store');
        Route::get('/printers/{printer}/edit', [PrinterController::class, 'edit'])->name('printers.edit');
        Route::put('/printers/{printer}', [PrinterController::class, 'update'])->name('printers.update');
        Route::delete('/printers/{printer}', [PrinterController::class, 'destroy'])->name('printers.destroy');
        Route::post('/printers/{printer}/check-status', [PrinterController::class, 'checkStatus'])->name('printers.check-status');
        Route::get('/printers/{printer}/health', [PrinterController::class, 'health'])->name('printers.health');

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });
});
