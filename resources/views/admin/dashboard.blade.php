@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('content')

<div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Job Hari Ini</div><div class="stat-value blue">{{ $todayStats['jobs_today'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Halaman Hari Ini</div><div class="stat-value blue">{{ $todayStats['pages_today'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Job Bulan Ini</div><div class="stat-value">{{ $monthStats['jobs_month'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Halaman Bulan Ini</div><div class="stat-value">{{ $monthStats['pages_month'] }}</div></div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr)">
    <div class="stat-card"><div class="stat-label">Sukses</div><div class="stat-value green">{{ $statusCounts['success'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Gagal</div><div class="stat-value red">{{ $statusCounts['failed'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Dibatalkan</div><div class="stat-value muted">{{ $statusCounts['cancelled'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Dalam Antrean</div><div class="stat-value blue">{{ $statusCounts['pending'] }}</div></div>
</div>

<div class="row-cards">
    <div class="card">
        <div class="card-header"><h3>Print per Hari (30 Hari)</h3></div>
        <div class="card-body"><canvas id="dailyChart" height="180"></canvas></div>
    </div>
    <div class="card">
        <div class="card-header"><h3>Top User Bulan Ini</h3></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nama</th><th>Job</th></tr></thead>
                <tbody>
                    @forelse($topUsers as $u)
                        <tr><td>{{ $u['name'] }}</td><td><strong>{{ $u['total_jobs'] }}</strong></td></tr>
                    @empty
                        <tr><td colspan="2" class="text-center text-muted">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header"><h3>Print per Bulan (12 Bulan)</h3></div>
    <div class="card-body"><canvas id="monthlyChart" height="120"></canvas></div>
</div>

@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const dd = @json($dailyChart);
new Chart(document.getElementById('dailyChart'), {
    type:'bar', data:{ labels:dd.labels, datasets:[{label:'Job',data:dd.jobs,backgroundColor:'rgba(59,130,246,.6)',borderRadius:3}] },
    options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}
});
const md = @json($monthlyChart);
new Chart(document.getElementById('monthlyChart'), {
    type:'line', data:{ labels:md.labels, datasets:[
        {label:'Job',data:md.jobs,borderColor:'#3b82f6',tension:.3,borderWidth:2},
        {label:'Halaman',data:md.pages,borderColor:'#10b981',tension:.3,borderWidth:2}
    ]}, options:{responsive:true}
});
</script>
@endpush
