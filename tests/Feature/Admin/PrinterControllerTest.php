<?php

namespace Tests\Feature\Admin;

use App\Models\Printer;
use App\Models\User;
use App\Services\CupsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class PrinterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_create_printer_form(): void
    {
        $this->actingAs($this->createAdmin())
            ->get(route('admin.printers.create'))
            ->assertOk()
            ->assertSee('Tambah Printer')
            ->assertSee('CNRCUPSIR2625ZK.ppd');
    }

    public function test_admin_can_create_printer_with_cups_sync(): void
    {
        $this->mock(CupsService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('createOrUpdatePrinter')->once();
        });

        $this->actingAs($this->createAdmin())
            ->post(route('admin.printers.store'), $this->validPrinterData())
            ->assertRedirect(route('admin.printers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('printers', [
            'cups_name' => 'Canon-Test',
            'driver' => 'CNRCUPSIR2625ZK.ppd',
            'connection_uri' => 'lpd://10.3.105.224',
        ]);
    }

    public function test_admin_can_update_printer_with_cups_sync(): void
    {
        $printer = Printer::create($this->validPrinterData(['cups_name' => 'Canon-Old']));

        $this->mock(CupsService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('createOrUpdatePrinter')->once();
        });

        $this->actingAs($this->createAdmin())
            ->put(route('admin.printers.update', $printer), $this->validPrinterData(['name' => 'Canon Updated']))
            ->assertRedirect(route('admin.printers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('printers', [
            'id' => $printer->id,
            'name' => 'Canon Updated',
        ]);
    }

    public function test_admin_can_delete_non_default_printer_with_cups_sync(): void
    {
        Printer::create($this->validPrinterData([
            'cups_name' => 'Canon-Default',
            'is_default' => true,
        ]));

        $printer = Printer::create($this->validPrinterData([
            'cups_name' => 'Canon-Delete',
            'is_default' => false,
        ]));

        $this->mock(CupsService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('deletePrinter')->once();
        });

        $this->actingAs($this->createAdmin())
            ->delete(route('admin.printers.destroy', $printer))
            ->assertRedirect(route('admin.printers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('printers', ['id' => $printer->id]);
    }

    public function test_unsafe_cups_name_is_rejected(): void
    {
        $this->actingAs($this->createAdmin())
            ->post(route('admin.printers.store'), $this->validPrinterData(['cups_name' => 'bad printer;rm']))
            ->assertSessionHasErrors('cups_name');
    }

    public function test_unsafe_connection_uri_scheme_is_rejected(): void
    {
        $this->actingAs($this->createAdmin())
            ->post(route('admin.printers.store'), $this->validPrinterData(['connection_uri' => 'file:///etc/passwd']))
            ->assertSessionHasErrors('connection_uri');
    }

    public function test_setting_default_unsets_other_defaults(): void
    {
        $oldDefault = Printer::create($this->validPrinterData([
            'cups_name' => 'Canon-Default',
            'is_default' => true,
        ]));

        $this->mock(CupsService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('createOrUpdatePrinter')->once();
        });

        $this->actingAs($this->createAdmin())
            ->post(route('admin.printers.store'), $this->validPrinterData([
                'cups_name' => 'Canon-New-Default',
                'is_default' => '1',
            ]))
            ->assertRedirect(route('admin.printers.index'));

        $this->assertFalse($oldDefault->fresh()->is_default);
        $this->assertDatabaseHas('printers', [
            'cups_name' => 'Canon-New-Default',
            'is_default' => true,
        ]);
    }

    private function createAdmin(): User
    {
        return User::create([
            'username' => 'admin-' . uniqid(),
            'name' => 'Admin',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function validPrinterData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Canon Test',
            'cups_name' => 'Canon-Test',
            'driver' => 'CNRCUPSIR2625ZK.ppd',
            'connection_uri' => 'lpd://10.3.105.224',
            'ip_address' => '10.3.105.224',
            'location' => 'Office',
            'is_default' => '0',
        ], $overrides);
    }
}
