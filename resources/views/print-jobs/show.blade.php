@extends('layouts.app')
@section('title', 'Detail Print Job')
@section('content')

@php
    $badge = match($printJob->status->value) {
        'previewing','processing','printing' => 'info',
        'ready' => 'primary',
        'waiting' => 'warning',
        'completed' => 'success',
        'failed' => 'danger',
        'cancelled' => 'secondary',
        default => 'secondary',
    };
    $hasPreview = $printJob->converted_pdf_path || strtolower($printJob->file_type) === 'pdf';
@endphp

<div class="flex gap-3 mb-3" style="align-items:center">
    <a href="{{ route('print-jobs.index') }}" class="btn btn-ghost btn-sm">← Kembali</a>
    <span id="jobStatusBadge" class="badge badge-{{ $badge }}" data-job-status style="font-size:13px;padding:5px 14px">
        {{ $printJob->status->label() }}
    </span>
    <span style="font-size:18px;font-weight:700;color:#1e293b">{{ $printJob->job_code }}</span>
</div>

<div class="row-cards">
    <div>
        <div class="card">
            <div class="card-header"><h3>Preview Dokumen</h3></div>
            <div class="card-body">
                @if($printJob->status === \App\Enums\PrintJobStatus::Ready && $hasPreview)
                    <iframe id="previewFrame" src="{{ route('print-jobs.preview', $printJob) }}" style="width:100%;height:620px;border:1px solid var(--border);border-radius:var(--radius);background:#fff"></iframe>
                    <div class="form-hint mt-3">Periksa format dokumen. Kalau sudah benar, klik Kirim ke Printer.</div>
                @elseif($printJob->status === \App\Enums\PrintJobStatus::Previewing)
                    <div class="alert alert-warning">⏳ Preview PDF sedang dibuat. Halaman akan memperbarui otomatis.</div>
                @elseif($printJob->status === \App\Enums\PrintJobStatus::Failed)
                    <div class="alert alert-danger">❌ Preview/print gagal: {{ $printJob->error_message }}</div>
                @elseif($hasPreview)
                    <iframe id="previewFrame" src="{{ route('print-jobs.preview', $printJob) }}" style="width:100%;height:620px;border:1px solid var(--border);border-radius:var(--radius);background:#fff"></iframe>
                @else
                    <div class="alert alert-warning">Preview belum tersedia.</div>
                @endif
            </div>
            <div class="card-footer flex gap-2" style="justify-content:space-between;flex-wrap:wrap">
                <div class="flex gap-2">
                    @if($hasPreview)
                        <a href="{{ route('print-jobs.preview', $printJob) }}" target="_blank" class="btn btn-primary btn-sm">📄 Buka Preview</a>
                        <a href="{{ route('print-jobs.download', $printJob) }}" class="btn btn-ghost btn-sm">⬇ Download PDF</a>
                    @endif
                </div>
                <div class="flex gap-2">
                    @if($printJob->status === \App\Enums\PrintJobStatus::Ready)
                        <form method="POST" action="{{ route('print-jobs.confirm', $printJob) }}" onsubmit="return confirm('Kirim dokumen ini ke printer {{ $printJob->printer?->name ?? 'terpilih' }}?')" class="flex gap-2" style="align-items:flex-end;flex-wrap:wrap">
                            @csrf
                            <div>
                                <label for="page_range" style="display:block;font-size:12px;font-weight:600;margin-bottom:4px">Halaman</label>
                                <input id="page_range" name="page_range" type="text" value="{{ old('page_range', $printJob->page_range) }}" placeholder="Semua / 1-3,5" style="max-width:160px">
                            </div>
                            <button type="submit" class="btn btn-success btn-sm">🖨️ Kirim ke Printer</button>
                        </form>
                    @endif
                    @if($printJob->isCancellable())
                        <form method="POST" action="{{ route('print-jobs.cancel', $printJob) }}" onsubmit="return confirm('Batalkan print job ini?')" style="display:inline">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">Batalkan Job</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Detail Job</h3></div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item"><label>File</label><div class="val">{{ $printJob->original_filename }}</div></div>
                    <div class="detail-item"><label>Tipe</label><div class="val">{{ strtoupper($printJob->file_type) }}</div></div>
                    <div class="detail-item"><label>Ukuran</label><div class="val">{{ $printJob->fileSizeFormatted() }}</div></div>
                    <div class="detail-item"><label>Halaman</label><div class="val">{{ $printJob->page_count ?? '—' }}</div></div>
                    <div class="detail-item"><label>Printer</label><div class="val">{{ $printJob->printer?->name ?? '—' }}</div></div>
                    <div class="detail-item"><label>Lokasi Printer</label><div class="val">{{ $printJob->printer?->location ?: '—' }}</div></div>
                    <div class="detail-item"><label>Copy</label><div class="val">{{ $printJob->copies }}×</div></div>
                    <div class="detail-item"><label>Kertas</label><div class="val">{{ $printJob->paper_size->value }}</div></div>
                    <div class="detail-item"><label>Orientasi</label><div class="val">{{ $printJob->orientation->label() }}</div></div>
                    <div class="detail-item"><label>Duplex</label><div class="val">{{ $printJob->duplex->label() }}</div></div>
                    <div class="detail-item"><label>Warna</label><div class="val">{{ $printJob->color_mode->label() }}</div></div>
                    <div class="detail-item"><label>Range</label><div class="val">{{ $printJob->page_range ?: 'Semua' }}</div></div>
                    <div class="detail-item"><label>Est. Lembar</label><div class="val">{{ $printJob->estimatedSheets() }}</div></div>
                </div>
                @if($printJob->error_message)
                    <div class="alert alert-danger mt-3">⚠️ {{ $printJob->error_message }}</div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Waktu</h3></div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item"><label>Upload</label><div class="val">{{ $printJob->submitted_at?->format('d/m/Y H:i:s') ?? '—' }}</div></div>
                    <div class="detail-item"><label>Mulai Proses</label><div class="val">{{ $printJob->processing_started_at?->format('H:i:s') ?? '—' }}</div></div>
                    <div class="detail-item"><label>Kirim ke Printer</label><div class="val">{{ $printJob->printed_at?->format('H:i:s') ?? '—' }}</div></div>
                    <div class="detail-item"><label>Selesai</label><div class="val">{{ $printJob->completed_at?->format('H:i:s') ?? '—' }}</div></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="height:fit-content">
        <div class="card-header"><h3>Log Status</h3></div>
        @forelse($printJob->logs as $log)
            <div class="log-item">
                <div class="log-header">
                    <span class="log-status">{{ ucfirst($log->status) }}</span>
                    <span class="log-time">{{ $log->created_at->format('d/m H:i:s') }}</span>
                </div>
                @if($log->message)
                    <div class="log-msg">{{ $log->message }}</div>
                @endif
            </div>
        @empty
            <div style="padding:20px;color:var(--muted);text-align:center;font-size:13px">Belum ada log.</div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
(() => {
    const badge = document.querySelector('[data-job-status]');
    const statusUrl = @json(route('print-jobs.status', $printJob));
    const refreshWhen = new Set(['previewing', 'waiting', 'processing', 'printing']);

    async function pollStatus() {
        const response = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
        if (!response.ok) return;

        const data = await response.json();
        badge.textContent = data.label;
        badge.className = `badge badge-${data.badge}`;

        if (data.status === 'ready' || data.isTerminal) {
            window.location.reload();
            return;
        }

        if (!refreshWhen.has(data.status)) {
            return;
        }

        window.setTimeout(pollStatus, 10000);
    }

    if (refreshWhen.has(@json($printJob->status->value))) {
        window.setTimeout(pollStatus, 10000);
    }
})();
</script>
@endpush
@endsection
