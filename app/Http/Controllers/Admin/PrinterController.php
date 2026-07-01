<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Printer;
use App\Services\CupsService;
use App\Services\SnmpPrinterService;
use Illuminate\Http\Request;

class PrinterController extends Controller
{
    public function index()
    {
        $printers = Printer::all();
        return view('admin.printers.index', compact('printers'));
    }

    public function edit(Printer $printer)
    {
        return view('admin.printers.edit', compact('printer'));
    }

    public function update(Request $request, Printer $printer)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cups_name' => ['required', 'string', 'max:100'],
            'connection_uri' => ['required', 'string', 'max:255'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_default' => ['boolean'],
        ]);

        $validated['is_default'] = $request->boolean('is_default');

        if ($validated['is_default']) {
            Printer::where('id', '!=', $printer->id)->update(['is_default' => false]);
        }

        $printer->update($validated);

        return redirect()->route('admin.printers.index')->with('success', 'Printer berhasil diperbarui.');
    }

    public function checkStatus(Printer $printer, CupsService $cupsService)
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
}
