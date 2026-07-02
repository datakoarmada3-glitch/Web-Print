<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Printer;
use App\Services\CupsService;
use App\Services\SnmpPrinterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class PrinterController extends Controller
{
    private const DEFAULT_DRIVER = 'CNRCUPSIR2625ZK.ppd';
    private const DEFAULT_URI = 'lpd://10.3.105.224';

    public function index()
    {
        $printers = Printer::orderByDesc('is_default')->orderBy('name')->get();

        return view('admin.printers.index', compact('printers'));
    }

    public function create()
    {
        $printer = new Printer([
            'driver' => self::DEFAULT_DRIVER,
            'connection_uri' => self::DEFAULT_URI,
        ]);

        return view('admin.printers.create', compact('printer'));
    }

    public function store(Request $request, CupsService $cupsService): RedirectResponse
    {
        $validated = $this->validatePrinter($request);
        $printer = new Printer($validated);

        try {
            $cupsService->createOrUpdatePrinter($printer);
        } catch (RuntimeException $exception) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menambahkan printer ke CUPS: ' . $exception->getMessage());
        }

        if ($printer->is_default) {
            Printer::query()->update(['is_default' => false]);
        }

        $printer->save();

        return redirect()
            ->route('admin.printers.index')
            ->with('success', 'Printer berhasil ditambahkan.');
    }

    public function edit(Printer $printer)
    {
        $printer->driver ??= self::DEFAULT_DRIVER;
        $printer->connection_uri ??= self::DEFAULT_URI;

        return view('admin.printers.edit', compact('printer'));
    }

    public function update(Request $request, Printer $printer, CupsService $cupsService): RedirectResponse
    {
        $validated = $this->validatePrinter($request, $printer);
        $printer->fill($validated);

        try {
            $cupsService->createOrUpdatePrinter($printer);
        } catch (RuntimeException $exception) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui printer di CUPS: ' . $exception->getMessage());
        }

        if ($printer->is_default) {
            Printer::where('id', '!=', $printer->id)->update(['is_default' => false]);
        }

        $printer->save();

        return redirect()
            ->route('admin.printers.index')
            ->with('success', 'Printer berhasil diperbarui.');
    }

    public function destroy(Printer $printer, CupsService $cupsService): RedirectResponse
    {
        if ($printer->is_default) {
            return back()->with('error', 'Printer default tidak bisa dihapus. Ganti printer default terlebih dahulu.');
        }

        if (Printer::count() <= 1) {
            return back()->with('error', 'Minimal harus ada satu printer terdaftar.');
        }

        if ($printer->printJobs()->exists()) {
            return back()->with('error', 'Printer yang sudah punya riwayat print tidak bisa dihapus. Nonaktifkan dari CUPS jika tidak dipakai.');
        }

        try {
            $cupsService->deletePrinter($printer);
        } catch (RuntimeException $exception) {
            return back()->with('error', 'Gagal menghapus printer dari CUPS: ' . $exception->getMessage());
        }

        $printer->delete();

        return redirect()
            ->route('admin.printers.index')
            ->with('success', 'Printer berhasil dihapus.');
    }

    public function checkStatus(Printer $printer, CupsService $cupsService): RedirectResponse
    {
        $status = $cupsService->getPrinterStatus($printer);
        $printer->update(['status' => $status]);

        return back()->with('success', "Status printer: {$status}");
    }

    public function health(Printer $printer, SnmpPrinterService $snmpService)
    {
        $info = $snmpService->getStatus($printer);

        return view('admin.printers.health', compact('printer', 'info'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePrinter(Request $request, ?Printer $printer = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cups_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('printers', 'cups_name')->ignore($printer?->id),
            ],
            'driver' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9._-]+(?:\.ppd)?$/'],
            'connection_uri' => ['required', 'string', 'max:255', 'regex:/^(lpd|ipp|ipps|socket):\/\/.+/i'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_default' => ['boolean'],
        ]);

        $validated['is_default'] = $request->boolean('is_default');
        $validated['driver'] = $validated['driver'] ?: self::DEFAULT_DRIVER;

        return $validated;
    }
}
