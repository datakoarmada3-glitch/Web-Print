<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'upload_max_size_mb' => ['required', 'integer', 'min:1', 'max:500'],
            'file_retention_days' => ['required', 'integer', 'min:1', 'max:365'],
            'allowed_file_extensions' => ['required', 'string'],
            'default_paper_size' => ['required', 'string'],
            'default_color_mode' => ['required', 'string'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::setValue($key, (string) $value);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
