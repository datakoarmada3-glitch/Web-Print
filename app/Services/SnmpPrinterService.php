<?php

namespace App\Services;

use App\Models\Printer;
use Symfony\Component\Process\Process;

class SnmpPrinterService
{
    private const TIMEOUT_SECONDS = 10;

    public function getStatus(Printer $printer): array
    {
        $host = $printer->ip_address;
        if (!$host) {
            return ['online' => false, 'message' => 'IP printer belum diatur.'];
        }

        $summary = $this->runSnmp($host, '1.3.6.1.2.1.25.3.5.1.1');
        $paperNames = $this->runSnmp($host, '1.3.6.1.2.1.43.8.2.1.13');
        $paperLevels = $this->runSnmp($host, '1.3.6.1.2.1.43.8.2.1.10');
        $paperMax = $this->runSnmp($host, '1.3.6.1.2.1.43.8.2.1.9');
        $markerNames = $this->runSnmp($host, '1.3.6.1.2.1.43.11.1.1.6');
        $markerLevels = $this->runSnmp($host, '1.3.6.1.2.1.43.11.1.1.9');
        $markerMax = $this->runSnmp($host, '1.3.6.1.2.1.43.11.1.1.8');

        return [
            'online' => $summary !== null || $paperNames !== null || $markerNames !== null,
            'message' => $summary ? 'SNMP aktif' : 'SNMP terbatas/tidak lengkap',
            'paper' => $this->buildIndexedItems($paperNames, $paperLevels, $paperMax),
            'toner' => $this->buildIndexedItems($markerNames, $markerLevels, $markerMax),
            'checked_at' => now()->toDateTimeString(),
        ];
    }

    private function runSnmp(string $host, string $oid): ?array
    {
        $process = new Process(['snmpwalk', '-v2c', '-c', 'public', '-Oqv', '-t', '2', '-r', '1', $host, $oid]);
        $process->setTimeout(self::TIMEOUT_SECONDS);
        $process->run();

        if (!$process->isSuccessful()) {
            return null;
        }

        $lines = array_filter(array_map('trim', explode("\n", $process->getOutput())));
        return $lines ?: null;
    }

    private function buildIndexedItems(?array $names, ?array $levels, ?array $maxValues): array
    {
        if (!$names) {
            return [];
        }

        return array_map(function (string $name, int $index) use ($levels, $maxValues): array {
            $level = $this->parseNumber($levels[$index] ?? null);
            $max = $this->parseNumber($maxValues[$index] ?? null);
            $percent = $this->calculatePercent($level, $max);

            return [
                'name' => trim($name, '"'),
                'level' => $level,
                'max' => $max,
                'percent' => $percent,
                'status' => $this->statusFromPercent($percent),
            ];
        }, array_values($names), array_keys(array_values($names)));
    }

    private function parseNumber(?string $value): ?int
    {
        if (!$value || !preg_match('/-?\d+/', $value, $matches)) {
            return null;
        }

        $number = (int) $matches[0];
        return $number >= 0 ? $number : null;
    }

    private function calculatePercent(?int $level, ?int $max): ?int
    {
        if (!$level || !$max || $max <= 0) {
            return null;
        }

        return max(0, min(100, (int) round(($level / $max) * 100)));
    }

    private function statusFromPercent(?int $percent): string
    {
        if ($percent === null) {
            return 'unknown';
        }

        if ($percent <= 10) {
            return 'critical';
        }

        if ($percent <= 25) {
            return 'low';
        }

        return 'ok';
    }
}
