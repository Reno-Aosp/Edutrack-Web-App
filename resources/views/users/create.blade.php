@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4" style="color:#5C1033;">Form Tambah User</h5>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control"
                        placeholder="Masukkan nama lengkap" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" name="email" class="form-control"
                        placeholder="email@example.com" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Password</label>
                    <input type="password" name="password" class="form-control"
                        placeholder="Min. 6 karakter" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Role</label>
                    <select name="role" class="form-select" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="admin">Admin</option>
                        <option value="dosen">Dosen</option>
                        <option value="mahasiswa">Mahasiswa</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn text-white fw-bold"
                    style="background:#E91E8C;">
                    <i class="bi bi-save"></i> Simpan
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection