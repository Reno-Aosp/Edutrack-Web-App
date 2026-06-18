@extends('layouts.app')

@section('title', 'Tambah Mahasiswa')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4" style="color:#5C1033;">Form Tambah Mahasiswa</h5>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('mahasiswa.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control"
                        placeholder="Nama lengkap mahasiswa" required>
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
                    <label class="form-label fw-bold">NIM</label>
                    <input type="text" name="nim" class="form-control"
                        placeholder="Nomor Induk Mahasiswa" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Program Studi</label>
                    <input type="text" name="prodi" class="form-control"
                        placeholder="Contoh: Teknik Informatika" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Angkatan</label>
                    <input type="number" name="angkatan" class="form-control"
                        placeholder="Contoh: 2024" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold">Kelas
                        <span class="text-muted small">(Opsional, bisa lebih dari 1)</span>
                    </label>
                    <div class="row g-2">
                        @forelse($kelas as $k)
                        <div class="col-md-3">
                            <div class="form-check border rounded-3 p-2">
                                <input class="form-check-input" type="checkbox"
                                    name="kelas_ids[]" value="{{ $k->id }}"
                                    id="kelas_{{ $k->id }}">
                                <label class="form-check-label fw-bold" for="kelas_{{ $k->id }}">
                                    {{ $k->nama_kelas }}
                                    <div class="text-muted small">{{ $k->prodi }}</div>
                                    <div class="text-muted small">Semester {{ $k->semester }}</div>
                                </label>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <p class="text-muted small">Belum ada kelas.
                                <a href="{{ route('kelas.create') }}">Tambah kelas dulu</a>
                            </p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn text-white fw-bold"
                    style="background:#E91E8C;">
                    <i class="bi bi-save"></i> Simpan
                </button>
                <a href="{{ route('mahasiswa.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection