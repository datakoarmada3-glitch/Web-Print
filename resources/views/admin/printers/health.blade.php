@extends('layouts.app')
@section('title', 'Printer Health')
@section('content')

<div class="flex gap-3 mb-3 items-center">
    <a href="{{ route('admin.printers.index') }}" class="btn btn-ghost btn-sm">← Kembali</a>
    <strong style="font-size:16px">Health: {{ $printer->name }}</strong>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Status SNMP</div>
        <div class="stat-value {{ $info['online'] ? 'green' : 'muted' }}">{{ $info['online'] ? 'Online' : 'Offline' }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">IP Printer</div>
        <div class="stat-value blue" style="font-size:24px">{{ $printer->ip_address }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Last Check</div>
        <div class="stat-value muted" style="font-size:18px">{{ $info['checked_at'] }}</div>
    </div>
</div>

<div class="row-cards">
    <div class="card">
        <div class="card-header"><h3>Paper / Tray Information</h3></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Tray</th><th>Level</th><th>Max</th><th>Persen</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($info['paper'] as $tray)
                        <tr>
                            <td>{{ $tray['name'] }}</td>
                            <td>{{ $tray['level'] ?? '—' }}</td>
                            <td>{{ $tray['max'] ?? '—' }}</td>
                            <td>{{ $tray['percent'] !== null ? $tray['percent'] . '%' : '—' }}</td>
                            <td><span class="badge badge-{{ $tray['status'] === 'ok' ? 'success' : ($tray['status'] === 'low' ? 'warning' : 'secondary') }}">{{ ucfirst($tray['status']) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted" style="padding:24px">SNMP paper information belum tersedia.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3>Toner / Consumable</h3></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Item</th><th>Level</th><th>Max</th><th>Persen</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($info['toner'] as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td>{{ $item['level'] ?? '—' }}</td>
                            <td>{{ $item['max'] ?? '—' }}</td>
                            <td>{{ $item['percent'] !== null ? $item['percent'] . '%' : '—' }}</td>
                            <td><span class="badge badge-{{ $item['status'] === 'ok' ? 'success' : ($item['status'] === 'low' ? 'warning' : 'secondary') }}">{{ ucfirst($item['status']) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted" style="padding:24px">SNMP toner information belum tersedia.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="alert alert-info mt-3">
    Data diambil via SNMP public. Jika kosong, aktifkan SNMP di Remote UI printer atau cek firewall jaringan.
</div>
@endsection
