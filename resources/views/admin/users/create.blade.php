@extends('layouts.app')
@section('title', 'Tambah User')
@section('content')

<div style="max-width:640px">
<form method="POST" action="{{ route('admin.users.store') }}" class="card">
    @csrf
    <div class="card-header"><h3>Tambah User Baru</h3></div>
    <div class="card-body">
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input @error('username') is-invalid @enderror" value="{{ old('username') }}" required>
                <div class="form-hint">Huruf, angka, strip, underscore</div>
                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="name" class="form-input @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input @error('password') is-invalid @enderror" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="form-input" required>
            </div>
            <div class="form-group full">
                <label class="form-label">Role</label>
                <select name="role" class="form-select"><option value="user">User</option><option value="admin">Admin</option></select>
            </div>
        </div>
    </div>
    <div class="card-footer text-end">
        <a href="{{ route('admin.users.index') }}" class="btn btn-ghost" style="margin-right:8px">Batal</a>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>
</div>
@endsection
