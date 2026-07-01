<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const LEGACY_CUPS_NAME = 'canon_ir2625';
    private const TARGET_CUPS_NAME = 'Canon-iR2625-2630-UFR-II';

    public function up(): void
    {
        $printerData = [
            'name' => 'Canon iR2625/2630 UFR II',
            'driver' => 'Canon UFR II',
            'connection_uri' => config('print.cups_printer_uri', 'lpd://10.3.105.224'),
            'ip_address' => config('print.printer_ip', '10.3.105.224'),
            'location' => 'Ruang Kerja Utama',
            'status' => 'unknown',
            'capabilities' => json_encode([
                'paper_sizes' => ['A4', 'Legal', 'F4'],
                'duplex' => true,
                'color' => false,
                'max_copies' => 99,
            ], JSON_THROW_ON_ERROR),
            'is_default' => true,
            'updated_at' => now(),
        ];

        $targetPrinter = DB::table('printers')
            ->where('cups_name', self::TARGET_CUPS_NAME)
            ->first();
        $legacyPrinter = DB::table('printers')
            ->where('cups_name', self::LEGACY_CUPS_NAME)
            ->first();

        if ($targetPrinter) {
            DB::table('printers')
                ->where('id', $targetPrinter->id)
                ->update($printerData);
            DB::table('printers')
                ->where('id', '!=', $targetPrinter->id)
                ->update(['is_default' => false, 'updated_at' => now()]);

            return;
        }

        if ($legacyPrinter) {
            DB::table('printers')
                ->where('id', $legacyPrinter->id)
                ->update([
                    ...$printerData,
                    'cups_name' => self::TARGET_CUPS_NAME,
                ]);
            DB::table('printers')
                ->where('id', '!=', $legacyPrinter->id)
                ->update(['is_default' => false, 'updated_at' => now()]);

            return;
        }

        DB::table('printers')->insert([
            ...$printerData,
            'cups_name' => self::TARGET_CUPS_NAME,
            'created_at' => now(),
        ]);
        DB::table('printers')
            ->where('cups_name', '!=', self::TARGET_CUPS_NAME)
            ->update(['is_default' => false, 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Intentionally do not restore the legacy RAW queue; it causes Canon #099 failures.
    }
};
