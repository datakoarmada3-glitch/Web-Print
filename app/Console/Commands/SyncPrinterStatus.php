<?php

namespace App\Console\Commands;

use App\Models\Printer;
use App\Services\CupsService;
use Illuminate\Console\Command;

class SyncPrinterStatus extends Command
{
    protected $signature = 'print:sync-printer-status';
    protected $description = 'Sync printer status from CUPS';

    public function handle(CupsService $cupsService): int
    {
        $printers = Printer::all();

        foreach ($printers as $printer) {
            $status = $cupsService->getPrinterStatus($printer);
            $printer->update(['status' => $status]);
            $this->line("{$printer->name}: {$status}");
        }

        $this->info('Printer statuses synced.');

        return self::SUCCESS;
    }
}
