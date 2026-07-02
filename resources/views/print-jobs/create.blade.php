@extends('layouts.app')
@section('title', 'Print Dokumen')
@section('content')

<div style="max-width:680px">
<form method="POST" action="{{ route('print-jobs.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="card">
        <div class="card-header"><h3>Upload Dokumen</h3></div>
        <div class="card-body">
            <div class="form-group full">
                <label class="form-label">Pilih File</label>
                <input type="file" name="document" class="form-input @error('document') is-invalid @enderror" required>
                <div class="form-hint">Format: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG · Maks {{ config('print.upload_max_size_mb', 50) }} MB</div>
                @error('document')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3>Opsi Print</h3></div>
        <div class="card-body">
            @if($printers->isEmpty())
                <div class="alert alert-warning">⚠️ Belum ada printer tersedia. Hubungi admin untuk menambahkan printer.</div>
            @endif

            <div class="form-grid">
                <div class="form-group full">
                    <label class="form-label">Pilih Printer</label>
                    <select name="printer_id" class="form-select @error('printer_id') is-invalid @enderror" {{ $printers->isEmpty() ? 'disabled' : '' }}>
                        @foreach($printers as $printer)
                            <option value="{{ $printer->id }}" {{ (string) old('printer_id', $defaultPrinterId) === (string) $printer->id ? 'selected' : '' }}>
                                {{ $printer->name }}{{ $printer->is_default ? ' — Default' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-hint">Nama printer bisa disesuaikan admin, misalnya Lantai 1, Lantai 2, atau area tertentu.</div>
                    @error('printer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Jumlah Copy</label>
                    <input type="number" name="copies" class="form-input @error('copies') is-invalid @enderror" min="1" max="99" value="{{ old('copies', 1) }}">
                    @error('copies')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Ukuran Kertas</label>
                    <select name="paper_size" class="form-select">
                        <option value="A4" {{ old('paper_size') === 'A4' ? 'selected' : '' }}>A4 (210 × 297 mm)</option>
                        <option value="F4" {{ old('paper_size') === 'F4' ? 'selected' : '' }}>F4 / Folio (215 × 330 mm)</option>
                        <option value="Legal" {{ old('paper_size') === 'Legal' ? 'selected' : '' }}>Legal (216 × 356 mm)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Orientasi</label>
                    <select name="orientation" class="form-select">
                        <option value="portrait" {{ old('orientation') === 'portrait' ? 'selected' : '' }}>Portrait (tegak)</option>
                        <option value="landscape" {{ old('orientation') === 'landscape' ? 'selected' : '' }}>Landscape (miring)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Duplex</label>
                    <select name="duplex" class="form-select">
                        <option value="none" {{ old('duplex') === 'none' ? 'selected' : '' }}>Satu Sisi</option>
                        <option value="long-edge" {{ old('duplex') === 'long-edge' ? 'selected' : '' }}>Dua Sisi – Tepi Panjang</option>
                        <option value="short-edge" {{ old('duplex') === 'short-edge' ? 'selected' : '' }}>Dua Sisi – Tepi Pendek</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Mode Warna</label>
                    <select name="color_mode" class="form-select">
                        <option value="grayscale" {{ old('color_mode') === 'grayscale' ? 'selected' : '' }}>Hitam Putih</option>
                        <option value="color" {{ old('color_mode') === 'color' ? 'selected' : '' }}>Berwarna</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Range Halaman <span class="text-muted">(opsional)</span></label>
                    <input type="text" name="page_range" class="form-input @error('page_range') is-invalid @enderror" value="{{ old('page_range') }}" placeholder="mis. 1-5 atau 1,3,5-10">
                    @error('page_range')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <div class="card-footer" style="text-align:right">
            <a href="{{ route('dashboard') }}" class="btn btn-ghost" style="margin-right:8px">Batal</a>
            <button type="submit" class="btn btn-primary" {{ $printers->isEmpty() ? 'disabled' : '' }}>🖨️ Kirim ke Antrean Print</button>
        </div>
    </div>
</form>
</div>
@endsection
