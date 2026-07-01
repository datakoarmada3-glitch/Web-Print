<?php

namespace App\Services;

use App\Models\PrintJob;
use App\Models\Printer;
use RuntimeException;
use Symfony\Component\Process\Process;

class CupsService
{
    private const PROCESS_TIMEOUT = 120;

    public function sendPrintJob(PrintJob $printJob, string $absolutePdfPath): string
    {
        $printer = $printJob->printer;

        $arguments = [
            'lp',
            '-d', $printer->cups_name,
            '-n', (string) $printJob->copies,
            '-o', 'PageSize=' . $printJob->paper_size->cupsMedia(),
            '-o', 'orientation-requested=' . $printJob->orientation->cupsCode(),
            '-o', 'sides=' . $printJob->duplex->cupsSides(),
            '-o', 'ColorModel=' . $printJob->color_mode->cupsColorModel(),
        ];

        if ($printJob->page_range) {
            $arguments[] = '-P';
            $arguments[] = $printJob->page_range;
        }

        $arguments[] = $absolutePdfPath;

        $output = $this->runProcess($arguments);
        $cupsJobId = $this->parseCupsJobId($output);

        if (!$cupsJobId) {
            throw new RuntimeException('CUPS job ID not found in lp output: ' . $output);
        }

        return $cupsJobId;
    }

    public function cancelJob(string $cupsJobId): void
    {
        $this->runProcess(['cancel', $cupsJobId]);
    }

    public function getPrinterStatus(Printer $printer): string
    {
        try {
            $output = $this->runProcess(['lpstat', '-p', $printer->cups_name, '-l']);
        } catch (RuntimeException) {
            return 'offline';
        }

        $lowerOutput = strtolower($output);

        if (str_contains($lowerOutput, 'disabled')) {
            return 'paused';
        }

        if (str_contains($lowerOutput, 'printing')) {
            return 'printing';
        }

        if (str_contains($lowerOutput, 'idle')) {
            return 'idle';
        }

        return 'online';
    }

    public function pausePrinter(Printer $printer): void
    {
        $this->runProcess(['cupsdisable', $printer->cups_name]);
    }

    public function resumePrinter(Printer $printer): void
    {
        $this->runProcess(['cupsenable', $printer->cups_name]);
    }

    private function runProcess(array $arguments): string
    {
        $process = new Process($arguments);
        $process->setTimeout(self::PROCESS_TIMEOUT);

        // Point CUPS commands to remote CUPS server (Docker container or external)
        $cupsServer = env('CUPS_SERVER');
        if ($cupsServer) {
            $process->setEnv(['CUPS_SERVER' => $cupsServer]);
        }

        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(trim($process->getErrorOutput()) ?: 'CUPS command failed.');
        }

        return trim($process->getOutput());
    }

    private function parseCupsJobId(string $output): ?string
    {
        if (!preg_match('/request id is\s+([^\s]+)/i', $output, $matches)) {
            return null;
        }

        return $matches[1];
    }
}
