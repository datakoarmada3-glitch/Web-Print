@extends('layouts.app')
@section('title', 'Laporan Penggunaan Print')
@section('content')

<div class="card mb-3">
    <div class="card-header flex justify-between items-center">
        <h3>Laporan Penggunaan Cetak</h3>
        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex items-center gap-2">
            <input type="month" name="month" value="{{ $month }}" class="form-input" onchange="this.form.submit()">
            <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
        </form>
    </div>
    <div class="card-body">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Job</div>
                <div class="stat-value blue">{{ number_format($summary['total_jobs']) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Halaman</div>
                <div class="stat-value green">{{ number_format($summary['total_pages']) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Selesai</div>
                <div class="stat-value green">{{ number_format($summary['completed']) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Gagal</div>
                <div class="stat-value red">{{ number_format($summary['failed']) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row-cards">
    <div class="card">
        <div class="card-header"><h3>Penggunaan per User</h3></div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>User</th><th class="text-end">Jumlah Job</th><th class="text-end">Estimasi Halaman</th></tr>
                </thead>
                <tbody>
                    @forelse($userStats as $stat)
                        <tr>
                            <td>{{ $stat->user?->name ?? 'Unknown' }} <span class="text-muted">({{ $stat->user?->username ?? '-' }})</span></td>
                            <td class="text-end">{{ $stat->total_jobs }}</td>
                            <td class="text-end">{{ $stat->total_pages }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted">Tidak ada data untuk bulan ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3>Penggunaan per Printer</h3></div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Printer</th><th>Lokasi</th><th class="text-end">Jumlah Job</th><th class="text-end">Estimasi Halaman</th></tr>
                </thead>
                <tbody>
                    @forelse($printerStats as $stat)
                        <tr>
                            <td>{{ $stat->printer?->name ?? 'Default' }}</td>
                            <td>{{ $stat->printer?->location ?: '—' }}</td>
                            <td class="text-end">{{ $stat->total_jobs }}</td>
                            <td class="text-end">{{ $stat->total_pages }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">Tidak ada data untuk bulan ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
