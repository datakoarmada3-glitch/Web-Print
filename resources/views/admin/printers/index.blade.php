@extends('layouts.app')
@section('title', 'Printer')
@section('content')

<div class="card">
    <div class="card-header">
        <div>
            <h3>Daftar Printer</h3>
            <div class="form-hint">Kelola printer database dan queue CUPS server.</div>
        </div>
        <a href="{{ route('admin.printers.create') }}" class="btn btn-primary">+ Tambah Printer</a>
    </div>

    @if($printers->isEmpty())
        <div class="card-body text-center">
            <p class="text-muted mb-3">Belum ada printer terdaftar.</p>
            <a href="{{ route('admin.printers.create') }}" class="btn btn-primary">Tambah Printer Pertama</a>
        </div>
    @else
        <div class="table-wrap desktop-table">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>CUPS</th>
                        <th>URI</th>
                        <th>Driver</th>
                        <th>Status</th>
                        <th>Default</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($printers as $printer)
                        <tr>
                            <td>
                                <strong>{{ $printer->name }}</strong>
                                <div class="form-hint">{{ $printer->location ?: 'Lokasi belum diisi' }}</div>
                            </td>
                            <td><code style="font-size:12px">{{ $printer->cups_name }}</code></td>
                            <td><code style="font-size:12px">{{ $printer->connection_uri }}</code></td>
                            <td><code style="font-size:12px">{{ $printer->driver ?: 'everywhere' }}</code></td>
                            <td><span class="badge badge-{{ match($printer->status) { 'online','idle'=>'success','printing'=>'info','error'=>'danger','paused'=>'warning',default=>'secondary' } }}">{{ $printer->statusLabel() }}</span></td>
                            <td>{!! $printer->is_default ? '<span class="badge badge-primary">Default</span>' : '<span class="text-muted">—</span>' !!}</td>
                            <td class="text-end">
                                <div class="flex gap-2" style="justify-content:flex-end">
                                    <a href="{{ route('admin.printers.health', $printer) }}" class="btn btn-primary btn-sm">Health</a>
                                    <a href="{{ route('admin.printers.edit', $printer) }}" class="btn btn-ghost btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.printers.check-status', $printer) }}" class="d-inline">@csrf<button class="btn btn-ghost btn-sm">Cek</button></form>
                                    <form method="POST" action="{{ route('admin.printers.destroy', $printer) }}" class="d-inline" onsubmit="return confirm('Hapus printer ini dari database dan CUPS?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm" {{ $printer->is_default ? 'disabled' : '' }}>Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mobile-cards card-body">
            @foreach($printers as $printer)
                <div class="mobile-card">
                    <div class="mobile-card-header">
                        <strong>{{ $printer->name }}</strong>
                        <span class="badge badge-{{ match($printer->status) { 'online','idle'=>'success','printing'=>'info','error'=>'danger','paused'=>'warning',default=>'secondary' } }}">{{ $printer->statusLabel() }}</span>
                    </div>
                    <div class="mobile-card-row"><span class="label">CUPS</span><code>{{ $printer->cups_name }}</code></div>
                    <div class="mobile-card-row"><span class="label">URI</span><code>{{ $printer->connection_uri }}</code></div>
                    <div class="mobile-card-row"><span class="label">Driver</span><code>{{ $printer->driver ?: 'everywhere' }}</code></div>
                    <div class="mobile-card-row"><span class="label">Default</span><span>{{ $printer->is_default ? 'Ya' : 'Tidak' }}</span></div>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <a href="{{ route('admin.printers.health', $printer) }}" class="btn btn-primary btn-sm">Health</a>
                        <a href="{{ route('admin.printers.edit', $printer) }}" class="btn btn-ghost btn-sm">Edit</a>
                        <form method="POST" action="{{ route('admin.printers.check-status', $printer) }}" class="d-inline">@csrf<button class="btn btn-ghost btn-sm">Cek</button></form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
