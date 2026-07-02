@extends('layouts.app')
@section('title', 'Riwayat Print')
@section('content')

<div class="card">
    <div class="card-header">
        <h3>Riwayat Print Saya</h3>
        <a href="{{ route('print-jobs.create') }}" class="btn btn-primary btn-sm">+ Print Baru</a>
    </div>

    {{-- Desktop table --}}
    <div class="desktop-table">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>File</th>
                        <th>Status</th>
                        <th>Printer</th>
                        <th>Kertas</th>
                        <th>Copy</th>
                        <th>Waktu</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($printJobs as $job)
                        <tr>
                            <td><code style="font-size:12px">{{ $job->job_code }}</code></td>
                            <td>{{ Str::limit($job->original_filename, 30) }}</td>
                            <td><span class="badge badge-{{ match($job->status->value) { 'waiting'=>'warning','processing','printing'=>'info','completed'=>'success','failed'=>'danger',default=>'secondary' } }}">{{ $job->status->label() }}</span></td>
                            <td>{{ $job->printer?->name ?? '—' }}</td>
                            <td>{{ $job->paper_size->value }}</td>
                            <td>{{ $job->copies }}</td>
                            <td class="text-muted">{{ $job->submitted_at?->format('d/m H:i') }}</td>
                            <td><a href="{{ route('print-jobs.show', $job) }}" class="btn btn-ghost btn-sm">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted" style="padding:32px">Belum ada riwayat print.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile cards --}}
    <div class="mobile-cards" style="padding:12px">
        @forelse($printJobs as $job)
            <a href="{{ route('print-jobs.show', $job) }}" class="mobile-card" style="display:block;text-decoration:none;color:inherit">
                <div class="mobile-card-header">
                    <code style="font-size:11px;color:var(--muted)">{{ $job->job_code }}</code>
                    <span class="badge badge-{{ match($job->status->value) { 'waiting'=>'warning','processing','printing'=>'info','completed'=>'success','failed'=>'danger',default=>'secondary' } }}">{{ $job->status->label() }}</span>
                </div>
                <div style="font-weight:500;margin-bottom:6px">{{ Str::limit($job->original_filename, 40) }}</div>
                <div class="mobile-card-row"><span class="label">Printer</span><span>{{ $job->printer?->name ?? '—' }}</span></div>
                <div class="mobile-card-row"><span class="label">Kertas</span><span>{{ $job->paper_size->value }} · {{ $job->copies }}×</span></div>
                <div class="mobile-card-row"><span class="label">Waktu</span><span>{{ $job->submitted_at?->format('d/m/Y H:i') }}</span></div>
            </a>
        @empty
            <div style="text-align:center;padding:32px;color:var(--muted)">Belum ada riwayat print.</div>
        @endforelse
    </div>

    @if($printJobs->hasPages())
        <div class="card-footer">{{ $printJobs->links() }}</div>
    @endif
</div>
@endsection
