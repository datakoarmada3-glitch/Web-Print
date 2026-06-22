@extends('layouts.app')
@section('title', 'Pengaturan')
@section('content')

<div style="max-width:760px">
<form method="POST" action="{{ route('admin.settings.update') }}" class="card">
    @csrf @method('PUT')
    <div class="card-header"><h3>Pengaturan Aplikasi</h3></div>
    <div class="card-body">
        <div class="form-grid">
            <div class="form-group"><label class="form-label">Maks Upload (MB)</label><input type="number" name="upload_max_size_mb" class="form-input" value="{{ old('upload_max_size_mb', $settings['upload_max_size_mb']->value ?? 50) }}" min="1" max="500" required></div>
            <div class="form-group"><label class="form-label">Retensi File (Hari)</label><input type="number" name="file_retention_days" class="form-input" value="{{ old('file_retention_days', $settings['file_retention_days']->value ?? 30) }}" min="1" max="365" required><div class="form-hint">File dihapus otomatis, histori tetap ada.</div></div>
            <div class="form-group full"><label class="form-label">Ekstensi Diizinkan</label><input type="text" name="allowed_file_extensions" class="form-input" value="{{ old('allowed_file_extensions', $settings['allowed_file_extensions']->value ?? 'pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png') }}" required><div class="form-hint">Pisahkan dengan koma.</div></div>
            <div class="form-group"><label class="form-label">Kertas Default</label><select name="default_paper_size" class="form-select">@foreach(['A4','Legal','F4'] as $size)<option value="{{ $size }}" {{ ($settings['default_paper_size']->value ?? 'A4') === $size ? 'selected' : '' }}>{{ $size }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">Warna Default</label><select name="default_color_mode" class="form-select"><option value="grayscale" {{ ($settings['default_color_mode']->value ?? 'grayscale') === 'grayscale' ? 'selected' : '' }}>Hitam Putih</option><option value="color" {{ ($settings['default_color_mode']->value ?? 'grayscale') === 'color' ? 'selected' : '' }}>Berwarna</option></select></div>
        </div>
    </div>
    <div class="card-footer text-end"><button type="submit" class="btn btn-primary">Simpan Pengaturan</button></div>
</form>
</div>
@endsection
