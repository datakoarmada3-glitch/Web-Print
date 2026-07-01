<?php

namespace Database\Seeders;

use App\Models\Printer;
use Illuminate\Database\Seeder;

class PrinterSeeder extends Seeder
{
    private const LEGACY_CUPS_NAME = 'canon_ir2625';

    public function run(): void
    {
        $cupsName = config('print.cups_printer_name', 'Canon-iR2625-2630-UFR-II');
        $printerData = [
            'name' => 'Canon iR2625/2630 UFR II',
            'driver' => 'Canon UFR II',
            'connection_uri' => config('print.cups_printer_uri', 'lpd://10.3.105.224'),
            'ip_address' => config('print.printer_ip', '10.3.105.224'),
            'location' => 'Ruang Kerja Utama',
            'status' => 'unknown',
            'capabilities' => [
                'paper_sizes' => ['A4', 'Legal', 'F4'],
                'duplex' => true,
                'color' => false, // Canon iR2625 is monochrome
                'max_copies' => 99,
            ],
            'is_default' => true,
        ];

        $targetPrinter = Printer::where('cups_name', $cupsName)->first();
        $legacyPrinter = Printer::where('cups_name', self::LEGACY_CUPS_NAME)->first();
        $printer = $targetPrinter ?? $legacyPrinter ?? new Printer();

        if (!$targetPrinter) {
            $printer->cups_name = $cupsName;
        }

        $printer->fill($printerData);
        $printer->save();

        Printer::where('id', '!=', $printer->id)->update(['is_default' => false]);
    }
}
