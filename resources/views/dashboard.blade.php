@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Print Hari Ini</div>
        <div class="stat-value blue">{{ $stats['total_today'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Print Bulan Ini</div>
        <div class="stat-value blue">{{ $stats['total_month'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Dalam Antrean</div>
        <div class="stat-value {{ $stats['pending'] > 0 ? 'muted' : 'green' }}">{{ $stats['pending'] }}</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Print Job Terakhir</h3>
        <a href="{{ route('print-jobs.create') }}" class="btn btn-primary btn-sm">+ Print Baru</a>
    </div>

    {{-- Desktop --}}
    <div class="desktop-table">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Kode</th><th>File</th><th>Status</th><th>Copy</th><th>Waktu</th></tr></thead>
                <tbody>
                    @forelse($recentJobs as $job)
                        <tr>
                            <td><a href="{{ route('print-jobs.show', $job) }}">{{ $job->job_code }}</a></td>
                            <td>{{ Str::limit($job->original_filename, 35) }}</td>
                            <td><span class="badge badge-{{ match($job->status->value) { 'waiting'=>'warning','processing','printing'=>'info','completed'=>'success','failed'=>'danger',default=>'secondary' } }}">{{ $job->status->label() }}</span></td>
                            <td>{{ $job->copies }}</td>
                            <td class="text-muted">{{ $job->submitted_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted" style="padding:32px">Belum ada print job. <a href="{{ route('print-jobs.create') }}">Print sekarang</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile --}}
    <div class="mobile-cards" style="padding:12px">
        @forelse($recentJobs as $job)
            <a href="{{ route('print-jobs.show', $job) }}" class="mobile-card" style="display:block;text-decoration:none;color:inherit">
                <div class="mobile-card-header">
                    <code style="font-size:11px;color:var(--muted)">{{ $job->job_code }}</code>
                    <span class="badge badge-{{ match($job->status->value) { 'waiting'=>'warning','processing','printing'=>'info','completed'=>'success','failed'=>'danger',default=>'secondary' } }}">{{ $job->status->label() }}</span>
                </div>
                <div style="font-weight:500">{{ Str::limit($job->original_filename, 40) }}</div>
                <div class="mobile-card-row" style="margin-top:4px"><span class="label">Copy</span><span>{{ $job->copies }}× · {{ $job->submitted_at?->format('d/m H:i') }}</span></div>
            </a>
        @empty
            <div style="text-align:center;padding:32px;color:var(--muted)">Belum ada print job. <a href="{{ route('print-jobs.create') }}">Print sekarang</a></div>
        @endforelse
    </div>
</div>
@endsection
