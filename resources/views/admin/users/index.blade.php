@extends('layouts.app')
@section('title', 'Kelola User')
@section('content')

<div class="card">
    <div class="card-header">
        <h3>Daftar User</h3>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">+ Tambah User</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Username</th><th>Nama</th><th>Role</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td><code style="font-size:12px">{{ $user->username }}</code></td>
                        <td>{{ $user->name }}</td>
                        <td><span class="badge badge-{{ $user->role === 'admin' ? 'purple' : 'primary' }}">{{ ucfirst($user->role) }}</span></td>
                        <td><span class="badge badge-{{ $user->is_active ? 'success' : 'secondary' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-ghost btn-sm">Edit</a>
                            <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}" class="d-inline">@csrf
                                <button class="btn btn-{{ $user->is_active ? 'warning' : 'success' }} btn-sm">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($users->hasPages())<div class="card-footer">{{ $users->links() }}</div>@endif
</div>
@endsection
