@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4" style="color:#5C1033;">Form Edit User</h5>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('users.update', $user->id) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control"
                        value="{{ $user->name }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" class="form-control"
                        value="{{ $user->email }}" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Role</label>
                    <select name="role" class="form-select" required>
                        <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="dosen" {{ $user->role == 'dosen' ? 'selected' : '' }}>Dosen</option>
                        <option value="mahasiswa" {{ $user->role == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Password Baru <span class="text-muted small">(kosongkan jika tidak diubah)</span></label>
                    <input type="password" name="password" class="form-control"
                        placeholder="Min. 6 karakter">
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn text-white fw-bold"
                    style="background:#E91E8C;">
                    <i class="bi bi-save"></i> Update
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection