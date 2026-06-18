@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="p-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4" style="background: linear-gradient(135deg, #E91E8C 0%, #F0A8D0 100%);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold text-white mb-1">Selamat Datang, {{ Auth::user()->name }}! 👋</h4>
                            <p class="text-white-50 mb-0">{{ Auth::user()->email }}</p>
                        </div>
                        <div class="display-4">📊</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards Row 1 -->
    <div class="row g-4 mb-4">
        <!-- Total Mahasiswa -->
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Mahasiswa</p>
                            <h3 class="fw-bold mb-0" style="color:#E91E8C;">{{ $totalMahasiswa }}</h3>
                        </div>
                        <div style="background:#FDE8F2; border-radius:12px; padding:12px;">
                            <i class="bi bi-people-fill fs-4" style="color:#E91E8C;"></i>
                        </div>
                    </div>
                    <small class="text-muted mt-3 d-block">Siswa terdaftar</small>
                </div>
            </div>
        </div>

        <!-- Total Mata Kuliah -->
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Mata Kuliah</p>
                            <h3 class="fw-bold mb-0" style="color:#FF6EC7;">{{ $totalMataKuliah }}</h3>
                        </div>
                        <div style="background:#FFE8F5; border-radius:12px; padding:12px;">
                            <i class="bi bi-book-fill fs-4" style="color:#FF6EC7;"></i>
                        </div>
                    </div>
                    <small class="text-muted mt-3 d-block">Mata kuliah aktif</small>
                </div>
            </div>
        </div>

        <!-- Total Dosen -->
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Dosen</p>
                            <h3 class="fw-bold mb-0" style="color:#8B5CF6;">{{ $totalDosen }}</h3>
                        </div>
                        <div style="background:#F3E8FF; border-radius:12px; padding:12px;">
                            <i class="bi bi-mortarboard-fill fs-4" style="color:#8B5CF6;"></i>
                        </div>
                    </div>
                    <small class="text-muted mt-3 d-block">Pengajar terdaftar</small>
                </div>
            </div>
        </div>

        <!-- Total Kelas -->
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Kelas</p>
                            <h3 class="fw-bold mb-0" style="color:#06B6D4;">{{ $totalKelas }}</h3>
                        </div>
                        <div style="background:#E0F7FA; border-radius:12px; padding:12px;">
                            <i class="bi bi-diagram-3-fill fs-4" style="color:#06B6D4;"></i>
                        </div>
                    </div>
                    <small class="text-muted mt-3 d-block">Kelas tersedia</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards Row 2 -->
    <div class="row g-4 mb-4">
        <!-- Total Absensi -->
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Absensi</p>
                            <h3 class="fw-bold mb-0" style="color:#10B981;">{{ $totalAbsensi }}</h3>
                        </div>
                        <div style="background:#ECFDF5; border-radius:12px; padding:12px;">
                            <i class="bi bi-check-circle-fill fs-4" style="color:#10B981;"></i>
                        </div>
                    </div>
                    <small class="text-muted mt-3 d-block">Catatan hadir</small>
                </div>
            </div>
        </div>

        <!-- Total Nilai -->
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Nilai</p>
                            <h3 class="fw-bold mb-0" style="color:#F59E0B;">{{ $totalNilai }}</h3>
                        </div>
                        <div style="background:#FFFBEB; border-radius:12px; padding:12px;">
                            <i class="bi bi-file-earmark-text-fill fs-4" style="color:#F59E0B;"></i>
                        </div>
                    </div>
                    <small class="text-muted mt-3 d-block">Hasil penilaian</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Guides -->
    <div class="row g-4">
        <!-- Panduan Cepat -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 p-4 pb-2">
                    <h5 class="fw-bold mb-0" style="color:#5C1033;">
                        <i class="bi bi-info-circle"></i> Panduan Cepat
                    </h5>
                </div>
                <div class="card-body p-4 pt-2">
                    <ul class="list-unstyled">
                        <li class="mb-3 d-flex gap-3">
                            <span style="color:#E91E8C; font-size:1.2rem;">👥</span>
                            <div>
                                <strong class="d-block">Kelola Mahasiswa</strong>
                                <small class="text-muted">Tambah, edit, dan kelola data mahasiswa</small>
                            </div>
                        </li>
                        <li class="mb-3 d-flex gap-3">
                            <span style="color:#FF6EC7; font-size:1.2rem;">📝</span>
                            <div>
                                <strong class="d-block">Input Nilai</strong>
                                <small class="text-muted">Masukkan nilai tugas, UTS, dan UAS</small>
                            </div>
                        </li>
                        <li class="mb-3 d-flex gap-3">
                            <span style="color:#8B5CF6; font-size:1.2rem;">📅</span>
                            <div>
                                <strong class="d-block">Catat Absensi</strong>
                                <small class="text-muted">Rekam kehadiran mahasiswa per kelas</small>
                            </div>
                        </li>
                        <li class="d-flex gap-3">
                            <span style="color:#06B6D4; font-size:1.2rem;">📚</span>
                            <div>
                                <strong class="d-block">Atur Kelas</strong>
                                <small class="text-muted">Kelola kelas dan mata kuliah</small>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Mahasiswa Terbaru -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 p-4 pb-2">
                    <h5 class="fw-bold mb-0" style="color:#5C1033;">
                        <i class="bi bi-star"></i> Mahasiswa Terbaru
                    </h5>
                </div>
                <div class="card-body p-4 pt-2">
                    @forelse($recentMahasiswa as $mhs)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div>
                            <p class="fw-bold mb-1">{{ $mhs->nama ?? $mhs->user->name ?? 'N/A' }}</p>
                            <small class="text-muted">NIM: {{ $mhs->nim }}</small>
                        </div>
                        <span class="badge bg-light text-dark">Baru</span>
                    </div>
                    @empty
                    <p class="text-muted text-center py-4">Belum ada mahasiswa terbaru</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-lift {
        transition: all 0.3s ease;
    }
    
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
    }

    .card {
        border-radius: 16px !important;
    }

    .card-header {
        border-radius: 16px 16px 0 0 !important;
    }
</style>
@endsection