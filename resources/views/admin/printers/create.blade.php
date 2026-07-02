@extends('layouts.app')
@section('title', 'Tambah Printer')
@section('content')

<div style="max-width:760px">
<form method="POST" action="{{ route('admin.printers.store') }}" class="card">
    @csrf
    <div class="card-header">
        <div>
            <h3>Tambah Printer</h3>
            <div class="form-hint">Form ini akan membuat queue CUPS baru via lpadmin.</div>
        </div>
    </div>
    <div class="card-body">
        <div class="form-grid">
            <div class="form-group full">
                <label class="form-label">Nama Printer</label>
                <input type="text" name="name" class="form-input @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Canon iR2625 Ruang Admin" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">CUPS Name</label>
                <input type="text" name="cups_name" class="form-input @error('cups_name') is-invalid @enderror" value="{{ old('cups_name', 'Canon-iR2625-2630-UFR-II') }}" required>
                <div class="form-hint">Huruf, angka, titik, underscore, dash. Tanpa spasi.</div>
                @error('cups_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">IP Address</label>
                <input type="text" name="ip_address" class="form-input @error('ip_address') is-invalid @enderror" value="{{ old('ip_address', '10.3.105.224') }}">
                @error('ip_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group full">
                <label class="form-label">Connection URI</label>
                <input type="text" name="connection_uri" class="form-input @error('connection_uri') is-invalid @enderror" value="{{ old('connection_uri', $printer->connection_uri) }}" required>
                <div class="form-hint">Contoh Canon aktif: lpd://10.3.105.224. Alternatif: ipp://IP/ipp/print atau socket://IP.</div>
                @error('connection_uri')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group full">
                <label class="form-label">Driver CUPS</label>
                <input type="text" name="driver" class="form-input @error('driver') is-invalid @enderror" value="{{ old('driver', $printer->driver) }}" required>
                <div class="form-hint">Default Canon iR2625/2630 UFR II: CNRCUPSIR2625ZK.ppd</div>
                @error('driver')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group full">
                <label class="form-label">Lokasi</label>
                <input type="text" name="location" class="form-input @error('location') is-invalid @enderror" value="{{ old('location') }}" placeholder="Ruang Admin">
                @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group full">
                <label style="display:flex;gap:8px;align-items:center">
                    <input type="hidden" name="is_default" value="0">
                    <input type="checkbox" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
                    Jadikan printer default
                </label>
            </div>
        </div>
    </div>
    <div class="card-footer text-end">
        <a href="{{ route('admin.printers.index') }}" class="btn btn-ghost" style="margin-right:8px">Batal</a>
        <button type="submit" class="btn btn-primary">Tambah Printer</button>
    </div>
</form>
</div>
@endsection
