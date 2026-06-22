<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CUPS Printer Configuration
    |--------------------------------------------------------------------------
    */
    'cups_printer_name' => env('CUPS_PRINTER_NAME', 'canon_ir2625'),
    'cups_printer_uri' => env('CUPS_PRINTER_URI', 'ipp://10.3.105.224/ipp/print'),
    'printer_ip' => env('PRINTER_IP', '10.3.105.224'),

    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    */
    'upload_max_size_mb' => (int) env('UPLOAD_MAX_SIZE_MB', 50),

    'allowed_extensions' => [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'jpg', 'jpeg', 'png',
    ],

    'allowed_mimes' => [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'image/jpeg',
        'image/png',
    ],

    /*
    |--------------------------------------------------------------------------
    | File Retention
    |--------------------------------------------------------------------------
    */
    'file_retention_days' => (int) env('FILE_RETENTION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Binary Paths
    |--------------------------------------------------------------------------
    */
    'libreoffice_bin' => env('LIBREOFFICE_BIN', '/usr/bin/libreoffice'),
    'pdfinfo_bin' => env('PDFINFO_BIN', '/usr/bin/pdfinfo'),
    'img2pdf_bin' => env('IMG2PDF_BIN', '/usr/bin/img2pdf'),

    /*
    |--------------------------------------------------------------------------
    | Paper Sizes
    |--------------------------------------------------------------------------
    */
    'paper_sizes' => [
        'A4' => 'A4',
        'Legal' => 'Legal',
        'F4' => 'Legal', // F4 mapped to Legal in CUPS
    ],

    /*
    |--------------------------------------------------------------------------
    | Print Options Mapping for CUPS
    |--------------------------------------------------------------------------
    */
    'orientation_map' => [
        'portrait' => '3',
        'landscape' => '4',
    ],

    'duplex_map' => [
        'none' => 'one-sided',
        'long-edge' => 'two-sided-long-edge',
        'short-edge' => 'two-sided-short-edge',
    ],

    'color_map' => [
        'color' => 'RGB',
        'grayscale' => 'Gray',
    ],
];
