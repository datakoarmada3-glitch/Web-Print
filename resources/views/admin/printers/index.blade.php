@extends('layouts.app')
@section('title', 'Printer')
@section('content')

<div class="card">
    <div class="card-header"><h3>Daftar Printer</h3></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nama</th><th>CUPS Name</th><th>IP</th><th>Status</th><th>Default</th><th></th></tr></thead>
            <tbody>
                @foreach($printers as $printer)
                    <tr>
                        <td>{{ $printer->name }}</td>
                        <td><code style="font-size:12px">{{ $printer->cups_name }}</code></td>
                        <td>{{ $printer->ip_address }}</td>
                        <td><span class="badge badge-{{ match($printer->status) { 'online','idle'=>'success','printing'=>'info','error'=>'danger','paused'=>'warning',default=>'secondary' } }}">{{ $printer->statusLabel() }}</span></td>
                        <td>{{ $printer->is_default ? '✓' : '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.printers.health', $printer) }}" class="btn btn-primary btn-sm">Health</a>
                            <a href="{{ route('admin.printers.edit', $printer) }}" class="btn btn-ghost btn-sm">Edit</a>
                            <form method="POST" action="{{ route('admin.printers.check-status', $printer) }}" class="d-inline">@csrf<button class="btn btn-primary btn-sm">Cek</button></form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
