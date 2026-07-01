<?php

namespace Database\Seeders;

use App\Models\Printer;
use Illuminate\Database\Seeder;

class PrinterSeeder extends Seeder
{
    public function run(): void
    {
        Printer::updateOrCreate(
            ['cups_name' => 'canon_ir2625'],
            [
                'name' => 'Canon iR2625',
                'driver' => 'Canon UFR II / RAW Socket',
                'connection_uri' => 'socket://10.3.105.224:9100',
                'ip_address' => '10.3.105.224',
                'location' => 'Ruang Kerja Utama',
                'status' => 'unknown',
                'capabilities' => [
                    'paper_sizes' => ['A4', 'Legal', 'F4'],
                    'duplex' => true,
                    'color' => false, // Canon iR2625 is monochrome
                    'max_copies' => 99,
                ],
                'is_default' => true,
            ]
        );
    }
}
