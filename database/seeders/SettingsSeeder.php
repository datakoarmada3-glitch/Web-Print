<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'upload_max_size_mb',
                'value' => '50',
                'type' => 'integer',
                'description' => 'Ukuran maksimal upload file (MB)',
            ],
            [
                'key' => 'file_retention_days',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Berapa hari file disimpan sebelum dihapus otomatis',
            ],
            [
                'key' => 'queue_paused',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Jeda antrean print (true = dijeda)',
            ],
            [
                'key' => 'allowed_file_extensions',
                'value' => 'pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png',
                'type' => 'string',
                'description' => 'Ekstensi file yang diizinkan (pisahkan dengan koma)',
            ],
            [
                'key' => 'default_paper_size',
                'value' => 'A4',
                'type' => 'string',
                'description' => 'Ukuran kertas default',
            ],
            [
                'key' => 'default_color_mode',
                'value' => 'grayscale',
                'type' => 'string',
                'description' => 'Mode warna default (grayscale/color)',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
