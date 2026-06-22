@extends('layouts.app')
@section('title', 'Detail Job: ' . $printJob->job_code)
@section('content')

<div class="flex gap-3 mb-3 items-center">
    <a href="{{ route('admin.history.index') }}" class="btn btn-ghost btn-sm">← Kembali</a>
    <span class="badge badge-{{ match($printJob->status->value) { 'waiting'=>'warning','processing','printing'=>'info','completed'=>'success','failed'=>'danger',default=>'secondary' } }}" style="font-size:13px;padding:5px 14px">{{ $printJob->status->label() }}</span>
    <strong style="font-size:16px">{{ $printJob->job_code }}</strong>
</div>

<div class="row-cards">
    <div>
        <div class="card">
            <div class="card-header"><h3>Info</h3></div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item"><label>User</label><div class="val">{{ $printJob->user->name }}</div></div>
                    <div class="detail-item"><label>Printer</label><div class="val">{{ $printJob->printer->name }}</div></div>
                    <div class="detail-item"><label>File</label><div class="val">{{ $printJob->original_filename }}</div></div>
                    <div class="detail-item"><label>Tipe</label><div class="val">{{ strtoupper($printJob->file_type) }}</div></div>
                    <div class="detail-item"><label>Ukuran</label><div class="val">{{ $printJob->fileSizeFormatted() }}</div></div>
                    <div class="detail-item"><label>Halaman</label><div class="val">{{ $printJob->page_count ?? '—' }}</div></div>
                    <div class="detail-item"><label>Copy</label><div class="val">{{ $printJob->copies }}×</div></div>
                    <div class="detail-item"><label>Kertas</label><div class="val">{{ $printJob->paper_size->value }}</div></div>
                    <div class="detail-item"><label>Orientasi</label><div class="val">{{ $printJob->orientation->label() }}</div></div>
                    <div class="detail-item"><label>Duplex</label><div class="val">{{ $printJob->duplex->label() }}</div></div>
                    <div class="detail-item"><label>Warna</label><div class="val">{{ $printJob->color_mode->label() }}</div></div>
                    <div class="detail-item"><label>Range</label><div class="val">{{ $printJob->page_range ?: 'Semua' }}</div></div>
                    <div class="detail-item"><label>Est. Lembar</label><div class="val">{{ $printJob->estimatedSheets() }}</div></div>
                    <div class="detail-item"><label>CUPS ID</label><div class="val">{{ $printJob->cups_job_id ?: '—' }}</div></div>
                    <div class="detail-item"><label>Submit</label><div class="val">{{ $printJob->submitted_at?->format('d/m/Y H:i:s') ?? '—' }}</div></div>
                    <div class="detail-item"><label>Selesai</label><div class="val">{{ $printJob->completed_at?->format('H:i:s') ?? '—' }}</div></div>
                </div>
                @if($printJob->error_message)
                    <div class="alert alert-danger mt-3">{{ $printJob->error_message }}</div>
                @endif
            </div>
            <div class="card-footer flex gap-2">
                @if($printJob->isCancellable())
                    <form method="POST" action="{{ route('admin.queue.cancel', $printJob) }}" onsubmit="return confirm('Batalkan?')">@csrf<button class="btn btn-danger btn-sm">Cancel</button></form>
                @endif
                @if($printJob->status->value === 'failed')
                    <form method="POST" action="{{ route('admin.queue.retry', $printJob) }}">@csrf<button class="btn btn-warning btn-sm">Retry</button></form>
                @endif
            </div>
        </div>
    </div>
    <div class="card" style="height:fit-content">
        <div class="card-header"><h3>Log</h3></div>
        @forelse($printJob->logs as $log)
            <div class="log-item">
                <div class="log-header"><span class="log-status">{{ ucfirst($log->status) }}</span><span class="log-time">{{ $log->created_at->format('d/m H:i:s') }}</span></div>
                @if($log->message)<div class="log-msg">{{ $log->message }}</div>@endif
            </div>
        @empty
            <div style="padding:20px;color:var(--muted);text-align:center;font-size:13px">Belum ada log.</div>
        @endforelse
    </div>
</div>
@endsection
