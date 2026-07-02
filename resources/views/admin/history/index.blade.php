@extends('layouts.app')
@section('title', 'Semua Riwayat Print')
@section('content')

<div class="card">
    <div class="card-header">
        <h3>Riwayat Print</h3>
        <form method="GET" class="flex gap-2 flex-wrap">
            <input type="text" name="search" class="form-input" style="width:200px" placeholder="Cari kode/file..." value="{{ request('search') }}">
            <select name="status" class="form-select" style="width:140px">
                <option value="">Semua Status</option>
                @foreach(['previewing','ready','waiting','processing','printing','completed','failed','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary btn-sm">Filter</button>
            @if(request()->hasAny(['search','status']))
                <a href="{{ route('admin.history.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            @endif
        </form>
    </div>

    <div class="desktop-table">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Kode</th><th>User</th><th>File</th><th>Printer</th><th>Tipe</th><th>Status</th><th>Waktu</th><th></th></tr></thead>
                <tbody>
                    @forelse($printJobs as $job)
                        <tr>
                            <td><code style="font-size:12px">{{ $job->job_code }}</code></td>
                            <td>{{ $job->user->name }}</td>
                            <td>{{ Str::limit($job->original_filename, 22) }}</td>
                            <td>{{ $job->printer?->name ?? '—' }}</td>
                            <td>{{ strtoupper($job->file_type) }}</td>
                            <td><span class="badge badge-{{ match($job->status->value) { 'previewing','processing','printing'=>'info','ready'=>'primary','waiting'=>'warning','completed'=>'success','failed'=>'danger',default=>'secondary' } }}" data-job-status data-job-id="{{ $job->id }}">{{ $job->status->label() }}</span></td>
                            <td class="text-muted">{{ $job->submitted_at?->format('d/m/Y H:i') }}</td>
                            <td><a href="{{ route('admin.history.show', $job) }}" class="btn btn-ghost btn-sm">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted" style="padding:24px">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mobile-cards" style="padding:12px">
        @forelse($printJobs as $job)
            <a href="{{ route('admin.history.show', $job) }}" class="mobile-card" style="display:block;text-decoration:none;color:inherit">
                <div class="mobile-card-header">
                    <code style="font-size:11px;color:var(--muted)">{{ $job->job_code }}</code>
                    <span class="badge badge-{{ match($job->status->value) { 'previewing','processing','printing'=>'info','ready'=>'primary','waiting'=>'warning','completed'=>'success','failed'=>'danger',default=>'secondary' } }}" data-job-status data-job-id="{{ $job->id }}">{{ $job->status->label() }}</span>
                </div>
                <div style="font-weight:500;margin-bottom:6px">{{ Str::limit($job->original_filename, 40) }}</div>
                <div class="mobile-card-row"><span class="label">User</span><span>{{ $job->user->name }}</span></div>
                <div class="mobile-card-row"><span class="label">Printer</span><span>{{ $job->printer?->name ?? '—' }}</span></div>
                <div class="mobile-card-row"><span class="label">Tipe</span><span>{{ strtoupper($job->file_type) }}</span></div>
                <div class="mobile-card-row"><span class="label">Waktu</span><span>{{ $job->submitted_at?->format('d/m/Y H:i') }}</span></div>
            </a>
        @empty
            <div style="text-align:center;padding:32px;color:var(--muted)">Tidak ada data.</div>
        @endforelse
    </div>

    @if($printJobs->hasPages())<div class="card-footer">{{ $printJobs->links() }}</div>@endif
</div>
@push('scripts')
<script>
(() => {
    const badges = [...document.querySelectorAll('[data-job-status]')];
    if (!badges.length) return;

    const terminal = new Set(['completed', 'failed', 'cancelled']);
    const statusUrl = @json(route('admin.history.statuses'));

    async function poll() {
        const response = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
        if (!response.ok) return;

        const jobs = await response.json();
        let hasActive = false;

        jobs.forEach(job => {
            const nodes = document.querySelectorAll(`[data-job-id="${job.id}"]`);
            nodes.forEach(node => {
                node.textContent = job.label;
                node.className = `badge badge-${job.badge}`;
            });
            if (!terminal.has(job.status)) {
                hasActive = true;
            }
        });

        if (hasActive) {
            window.setTimeout(poll, 10000);
        }
    }

    window.setTimeout(poll, 10000);
})();
</script>
@endpush
@endsection
