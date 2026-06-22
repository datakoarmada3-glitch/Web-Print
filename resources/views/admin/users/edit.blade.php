@extends('layouts.app')
@section('title', 'Edit User')
@section('content')

<div style="max-width:640px">
<form method="POST" action="{{ route('admin.users.update', $user) }}" class="card">
    @csrf @method('PUT')
    <div class="card-header"><h3>Edit User</h3></div>
    <div class="card-body">
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}" required>
                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="name" class="form-input @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group full">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                    <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-footer text-end">
        <a href="{{ route('admin.users.index') }}" class="btn btn-ghost" style="margin-right:8px">Batal</a>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>

<form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="card mt-3">
    @csrf
    <div class="card-header"><h3>Reset Password</h3></div>
    <div class="card-body">
        <label class="form-label">Password Baru</label>
        <input type="password" name="new_password" class="form-input @error('new_password') is-invalid @enderror" required minlength="6">
        @error('new_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="card-footer text-end"><button type="submit" class="btn btn-warning">Reset Password</button></div>
</form>
</div>
@endsection
