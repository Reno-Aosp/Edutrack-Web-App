@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row g-4">
    <!-- Card Total Mahasiswa -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div style="background:#FDE8F2; border-radius:12px; padding:16px;">
                    <i class="bi bi-people-fill fs-2" style="color:#E91E8C;"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Mahasiswa</div>
                    <div class="fw-bold fs-2">{{ $totalMahasiswa }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Total Mata Kuliah -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div style="background:#FDE8F2; border-radius:12px; padding:16px;">
                    <i class="bi bi-book-fill fs-2" style="color:#E91E8C;"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Mata Kuliah</div>
                    <div class="fw-bold fs-2">{{ $totalMataKuliah }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Selamat Datang -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4" style="background:#F0A8D0;">
    <div class="card-body p-4">
        <div style="color:#5C1033;" class="small">Selamat Datang</div>
        <div class="fw-bold fs-5" style="color:#5C1033;">{{ Auth::user()->name }}</div>
        <div style="color:#E91E8C;" class="small">{{ Auth::user()->email }}</div>
    </div>
</div>
    </div>
</div>

<!-- Info -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color:#5C1033;">
                    <i class="bi bi-info-circle"></i> Panduan Cepat
                </h5>
                <ul class="list-unstyled">
                    <li class="mb-2">📋 <strong>Mahasiswa</strong> — Tambah dan kelola data mahasiswa</li>
                    <li class="mb-2">📝 <strong>Nilai</strong> — Input nilai tugas, UTS, dan UAS mahasiswa</li>
                    <li class="mb-2">📅 <strong>Absensi</strong> — Catat kehadiran mahasiswa per mata kuliah</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection