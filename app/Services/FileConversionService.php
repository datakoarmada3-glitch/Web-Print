<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;

class FileConversionService
{
    private const PROCESS_TIMEOUT = 180;

    private array $officeExtensions = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
    private array $imageExtensions = ['jpg', 'jpeg', 'png'];

    public function needsConversion(string $fileType): bool
    {
        $ext = strtolower($fileType);
        return in_array($ext, $this->officeExtensions) || in_array($ext, $this->imageExtensions);
    }

    public function convertToPdf(string $storagePath, string $fileType): string
    {
        $ext = strtolower($fileType);
        $absolutePath = Storage::disk('local')->path($storagePath);

        if (! file_exists($absolutePath)) {
            throw new RuntimeException("Source file not found: {$storagePath}");
        }

        if (in_array($ext, $this->officeExtensions)) {
            return $this->convertOffice($absolutePath, $storagePath);
        }

        if (in_array($ext, $this->imageExtensions)) {
            return $this->convertImage($absolutePath, $storagePath);
        }

        throw new RuntimeException("Unsupported file type for conversion: {$ext}");
    }

    public function getPageCount(string $pdfStoragePath): ?int
    {
        $absolutePath = Storage::disk('local')->path($pdfStoragePath);

        if (! file_exists($absolutePath)) {
            return null;
        }

        $pdfinfoBin = config('print.pdfinfo_bin', '/usr/bin/pdfinfo');
        $process = new Process([$pdfinfoBin, $absolutePath]);
        $process->setTimeout(30);
        $process->run();

        if (! $process->isSuccessful()) {
            return null;
        }

        $output = $process->getOutput();
        if (preg_match('/Pages:\s*(\d+)/', $output, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function convertOffice(string $absolutePath, string $storagePath): string
    {
        $outputDir = dirname($absolutePath);
        $libreOfficeBin = config('print.libreoffice_bin', '/usr/bin/libreoffice');
        $profileDir = storage_path('framework/cache/lo_profile');

        if (! is_dir($profileDir)) {
            @mkdir($profileDir, 0755, true);
        }

        $profileUri = 'file://' . $profileDir;

        $process = new Process([
            $libreOfficeBin,
            "-env:UserInstallation={$profileUri}",
            '--headless',
            '--nologo',
            '--nofirststartwizard',
            '--convert-to', 'pdf:writer_pdf_Export:{"SelectPdfVersion":{"type":"long","value":"1"},"EmbedStandardFonts":{"type":"boolean","value":"true"}}',
            '--outdir', $outputDir,
            $absolutePath,
        ]);

        $process->setTimeout(self::PROCESS_TIMEOUT);
        $process->setEnv(['HOME' => $profileDir]);
        $process->run();

        if (! $process->isSuccessful()) {
            // Fallback to basic pdf conversion if filter args fail
            $fallbackProcess = new Process([
                $libreOfficeBin,
                "-env:UserInstallation={$profileUri}",
                '--headless',
                '--nologo',
                '--nofirststartwizard',
                '--convert-to', 'pdf',
                '--outdir', $outputDir,
                $absolutePath,
            ]);
            $fallbackProcess->setTimeout(self::PROCESS_TIMEOUT);
            $fallbackProcess->setEnv(['HOME' => $profileDir]);
            $fallbackProcess->run();

            if (! $fallbackProcess->isSuccessful()) {
                throw new RuntimeException(
                    'LibreOffice conversion failed: ' . trim($fallbackProcess->getErrorOutput() ?: $process->getErrorOutput())
                );
            }
        }

        $pdfFilename = pathinfo($absolutePath, PATHINFO_FILENAME) . '.pdf';
        $pdfAbsolutePath = $outputDir . '/' . $pdfFilename;

        if (! file_exists($pdfAbsolutePath)) {
            throw new RuntimeException('Converted PDF file not found after LibreOffice conversion.');
        }

        return dirname($storagePath) . '/' . $pdfFilename;
    }

    private function convertImage(string $absolutePath, string $storagePath): string
    {
        $outputDir = dirname($absolutePath);
        $pdfFilename = pathinfo($absolutePath, PATHINFO_FILENAME) . '.pdf';
        $pdfAbsolutePath = $outputDir . '/' . $pdfFilename;

        $img2pdfBin = config('print.img2pdf_bin', '/usr/bin/img2pdf');

        if (file_exists($img2pdfBin)) {
            $process = new Process([
                $img2pdfBin,
                '--pagesize', 'A4',
                '--auto-orient',
                '--output', $pdfAbsolutePath,
                $absolutePath,
            ]);

            $process->setTimeout(self::PROCESS_TIMEOUT);
            $process->run();

            if ($process->isSuccessful() && file_exists($pdfAbsolutePath)) {
                return dirname($storagePath) . '/' . $pdfFilename;
            }
        }

        $process = new Process([
            'convert',
            $absolutePath,
            '-resize', '595x842>',
            '-gravity', 'center',
            '-extent', '595x842',
            '-units', 'PixelsPerInch',
            '-density', '72',
            $pdfAbsolutePath,
        ]);

        $process->setTimeout(self::PROCESS_TIMEOUT);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException(
                'Image to PDF conversion failed: ' . trim($process->getErrorOutput())
            );
        }

        if (! file_exists($pdfAbsolutePath)) {
            throw new RuntimeException('PDF file not found after image conversion.');
        }

        return dirname($storagePath) . '/' . $pdfFilename;
    }
}
