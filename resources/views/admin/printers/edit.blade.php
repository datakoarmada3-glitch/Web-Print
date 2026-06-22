@extends('layouts.app')
@section('title', 'Edit Printer')
@section('content')

<div style="max-width:680px">
<form method="POST" action="{{ route('admin.printers.update', $printer) }}" class="card">
    @csrf @method('PUT')
    <div class="card-header"><h3>Edit Printer</h3></div>
    <div class="card-body">
        <div class="form-grid">
            <div class="form-group full"><label class="form-label">Nama Printer</label><input type="text" name="name" class="form-input" value="{{ old('name', $printer->name) }}" required></div>
            <div class="form-group"><label class="form-label">CUPS Name</label><input type="text" name="cups_name" class="form-input" value="{{ old('cups_name', $printer->cups_name) }}" required></div>
            <div class="form-group"><label class="form-label">IP Address</label><input type="text" name="ip_address" class="form-input" value="{{ old('ip_address', $printer->ip_address) }}"></div>
            <div class="form-group full"><label class="form-label">Connection URI</label><input type="text" name="connection_uri" class="form-input" value="{{ old('connection_uri', $printer->connection_uri) }}" required><div class="form-hint">Contoh: ipp://10.3.105.224/ipp/print</div></div>
            <div class="form-group full"><label class="form-label">Lokasi</label><input type="text" name="location" class="form-input" value="{{ old('location', $printer->location) }}"></div>
            <div class="form-group full"><label style="display:flex;gap:8px;align-items:center"><input type="hidden" name="is_default" value="0"><input type="checkbox" name="is_default" value="1" {{ $printer->is_default ? 'checked' : '' }}> Jadikan printer default</label></div>
        </div>
    </div>
    <div class="card-footer text-end"><a href="{{ route('admin.printers.index') }}" class="btn btn-ghost" style="margin-right:8px">Batal</a><button type="submit" class="btn btn-primary">Simpan</button></div>
</form>
</div>
@endsection
