@extends('layouts.app')
@section('title', 'Antrean Print')
@section('content')

<div class="card">
    <div class="card-header">
        <h3>Antrean Aktif</h3>
        <div class="flex gap-2">
            @if($isPaused)
                <form method="POST" action="{{ route('admin.queue.resume') }}" class="d-inline">@csrf <button class="btn btn-success btn-sm">▶ Resume</button></form>
            @else
                <form method="POST" action="{{ route('admin.queue.pause') }}" class="d-inline">@csrf <button class="btn btn-warning btn-sm">⏸ Pause</button></form>
            @endif
        </div>
    </div>
    @if($isPaused)
        <div class="alert alert-warning" style="margin:12px 18px">⚠️ Antrean dijeda. Job baru tidak diproses.</div>
    @endif

    <div class="desktop-table">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Kode</th><th>User</th><th>File</th><th>Status</th><th>Waktu</th><th></th></tr></thead>
                <tbody>
                    @forelse($activeJobs as $job)
                        <tr>
                            <td><code style="font-size:12px">{{ $job->job_code }}</code></td>
                            <td>{{ $job->user->name }}</td>
                            <td>{{ Str::limit($job->original_filename, 25) }}</td>
                            <td><span class="badge badge-{{ match($job->status->value) { 'waiting'=>'warning','processing','printing'=>'info',default=>'secondary' } }}">{{ $job->status->label() }}</span></td>
                            <td class="text-muted">{{ $job->submitted_at?->format('H:i') }}</td>
                            <td>
                                @if($job->isCancellable())
                                    <form method="POST" action="{{ route('admin.queue.cancel', $job) }}" class="d-inline" onsubmit="return confirm('Batalkan?')">@csrf<button class="btn btn-danger btn-sm">Cancel</button></form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted" style="padding:24px">Tidak ada job dalam antrean.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mobile-cards" style="padding:12px">
        @forelse($activeJobs as $job)
            <div class="mobile-card">
                <div class="mobile-card-header">
                    <code style="font-size:11px;color:var(--muted)">{{ $job->job_code }}</code>
                    <span class="badge badge-{{ match($job->status->value) { 'waiting'=>'warning','processing','printing'=>'info',default=>'secondary' } }}">{{ $job->status->label() }}</span>
                </div>
                <div style="font-weight:500;margin-bottom:6px">{{ Str::limit($job->original_filename, 40) }}</div>
                <div class="mobile-card-row"><span class="label">User</span><span>{{ $job->user->name }}</span></div>
                <div class="mobile-card-row"><span class="label">Waktu</span><span>{{ $job->submitted_at?->format('d/m H:i') }}</span></div>
                @if($job->isCancellable())
                    <form method="POST" action="{{ route('admin.queue.cancel', $job) }}" style="margin-top:10px" onsubmit="return confirm('Batalkan?')">@csrf<button class="btn btn-danger btn-sm" style="width:100%;justify-content:center">Cancel Job</button></form>
                @endif
            </div>
        @empty
            <div style="text-align:center;padding:32px;color:var(--muted)">Tidak ada job dalam antrean.</div>
        @endforelse
    </div>

    @if($activeJobs->hasPages())<div class="card-footer">{{ $activeJobs->links() }}</div>@endif
</div>
@endsection
