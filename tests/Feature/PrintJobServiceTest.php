<?php

namespace Tests\Feature;

use App\Enums\PrintJobStatus;
use App\Jobs\ProcessPrintJob;
use App\Models\Printer;
use App\Models\User;
use App\Services\FileConversionService;
use App\Services\FileUploadService;
use App\Services\PrintJobService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use Tests\TestCase;

class PrintJobServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_job_uses_selected_printer_without_dispatching_print(): void
    {
        Queue::fake();
        $user = $this->createUser();
        $selectedPrinter = Printer::create($this->printerData(['cups_name' => 'Epson-L5190']));
        Printer::create($this->printerData([
            'cups_name' => 'Canon-Default',
            'is_default' => true,
        ]));

        $service = $this->makeServiceWithStoredUpload(needsConversion: false);
        $printJob = $service->createPreviewJob($user->id, UploadedFile::fake()->create('test.pdf'), $this->printOptions([
            'printer_id' => $selectedPrinter->id,
        ]));

        $this->assertSame($selectedPrinter->id, $printJob->printer_id);
        $this->assertSame(PrintJobStatus::Ready, $printJob->status);
        Queue::assertNotPushed(ProcessPrintJob::class);
    }

    public function test_confirm_ready_job_dispatches_print(): void
    {
        Queue::fake();
        $user = $this->createUser();
        $printer = Printer::create($this->printerData(['is_default' => true]));
        $service = $this->makeServiceWithStoredUpload(needsConversion: false);
        $printJob = $service->createPreviewJob($user->id, UploadedFile::fake()->create('test.pdf'), $this->printOptions([
            'printer_id' => $printer->id,
        ]));

        $service->confirmJob($printJob);

        $this->assertSame(PrintJobStatus::Waiting, $printJob->fresh()->status);
        Queue::assertPushed(ProcessPrintJob::class);
    }

    public function test_create_job_falls_back_to_default_printer_without_selection(): void
    {
        Queue::fake();
        $user = $this->createUser();
        $defaultPrinter = Printer::create($this->printerData([
            'cups_name' => 'Canon-Default',
            'is_default' => true,
        ]));
        Printer::create($this->printerData(['cups_name' => 'Epson-L5190']));

        $service = $this->makeServiceWithStoredUpload(needsConversion: false);
        $printJob = $service->createJob($user->id, UploadedFile::fake()->create('test.pdf'), $this->printOptions());

        $this->assertSame($defaultPrinter->id, $printJob->printer_id);
        Queue::assertPushed(ProcessPrintJob::class);
    }

    private function makeServiceWithStoredUpload(bool $needsConversion): PrintJobService
    {
        $this->mock(FileUploadService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('storeUploadedFile')->andReturn([
                'original_filename' => 'test.pdf',
                'stored_original_path' => 'print-jobs/originals/test.pdf',
                'file_type' => 'pdf',
                'file_size' => 1024,
                'mime_type' => 'application/pdf',
            ]);
        });

        $this->mock(FileConversionService::class, function (MockInterface $mock) use ($needsConversion): void {
            $mock->shouldReceive('needsConversion')->andReturn($needsConversion);
            $mock->shouldReceive('getPageCount')->andReturn(1);
        });

        return app(PrintJobService::class);
    }

    private function createUser(): User
    {
        return User::create([
            'username' => 'user-' . uniqid(),
            'name' => 'User',
            'password' => 'password',
            'role' => 'user',
            'is_active' => true,
        ]);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function printerData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Printer Test',
            'cups_name' => 'Printer-Test',
            'driver' => 'everywhere',
            'connection_uri' => 'ipp://10.3.104.33/ipp/print',
            'ip_address' => '10.3.104.33',
            'location' => 'Lantai 1',
            'is_default' => false,
        ], $overrides);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function printOptions(array $overrides = []): array
    {
        return array_merge([
            'copies' => 1,
            'paper_size' => 'A4',
            'orientation' => 'portrait',
            'duplex' => 'none',
            'color_mode' => 'grayscale',
            'page_range' => null,
        ], $overrides);
    }
}
