@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="p-4">

    {{-- ── Welcome Banner ── --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4"
                style="background: linear-gradient(135deg, #E91E8C 0%, #F0A8D0 100%);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold text-white mb-1">
                                Selamat Datang, {{ Auth::user()->name }}! 👋
                            </h4>
                            <p class="text-white-50 mb-0">{{ Auth::user()->email }}</p>
                        </div>
                        <div class="display-4">
                            @if(Auth::user()->role === 'dosen') 🧑‍🏫 @else 📊 @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         DASHBOARD ADMIN
    ══════════════════════════════════════════════════════ --}}
    @if(Auth::user()->role === 'admin')

    <div class="row g-4 mb-4">
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

    <div class="row g-4 mb-4">
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

    <div class="row g-4">
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

    {{-- ══════════════════════════════════════════════════════
         DASHBOARD DOSEN
    ══════════════════════════════════════════════════════ --}}
    @elseif(Auth::user()->role === 'dosen')

    {{-- Stat cards dosen --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Mata Kuliah Diampu</p>
                            <h3 class="fw-bold mb-0" style="color:#E91E8C;">
                                {{ $dosenMatkul->count() }}
                            </h3>
                        </div>
                        <div style="background:#FDE8F2; border-radius:12px; padding:12px;">
                            <i class="bi bi-book-fill fs-4" style="color:#E91E8C;"></i>
                        </div>
                    </div>
                    <small class="text-muted mt-3 d-block">Semester ini</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Kelas Diajar</p>
                            <h3 class="fw-bold mb-0" style="color:#8B5CF6;">
                                {{ $dosenKelas->count() }}
                            </h3>
                        </div>
                        <div style="background:#F3E8FF; border-radius:12px; padding:12px;">
                            <i class="bi bi-diagram-3-fill fs-4" style="color:#8B5CF6;"></i>
                        </div>
                    </div>
                    <small class="text-muted mt-3 d-block">Kelas aktif</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Mahasiswa</p>
                            <h3 class="fw-bold mb-0" style="color:#10B981;">
                                {{ $dosenTotalMahasiswa }}
                            </h3>
                        </div>
                        <div style="background:#ECFDF5; border-radius:12px; padding:12px;">
                            <i class="bi bi-people-fill fs-4" style="color:#10B981;"></i>
                        </div>
                    </div>
                    <small class="text-muted mt-3 d-block">Di semua kelas yang diajar</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Mata Kuliah yang diampu --}}
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 p-4 pb-2">
                    <h5 class="fw-bold mb-0" style="color:#5C1033;">
                        <i class="bi bi-book"></i> Mata Kuliah Saya
                    </h5>
                </div>
                <div class="card-body p-4 pt-2">
                    @forelse($dosenMatkul as $mk)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div>
                            <p class="fw-bold mb-1" style="color:#5C1033;">{{ $mk->nama }}</p>
                            <small class="text-muted">{{ $mk->kode }} · {{ $mk->sks }} SKS</small>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">
                                {{ $mk->kelas->count() }} kelas
                            </small>
                            @foreach($mk->kelas as $k)
                                <span class="badge" style="background:#FDE8F2; color:#E91E8C;">
                                    {{ $k->nama_kelas }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3">Belum ada mata kuliah</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Kelas yang diajar --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 p-4 pb-2">
                    <h5 class="fw-bold mb-0" style="color:#5C1033;">
                        <i class="bi bi-diagram-3"></i> Kelas Saya
                    </h5>
                </div>
                <div class="card-body p-4 pt-2">
                    @forelse($dosenKelas as $kls)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div>
                            <p class="fw-bold mb-1" style="color:#5C1033;">{{ $kls->nama_kelas }}</p>
                            <small class="text-muted">
                                {{ $kls->prodi }} · Angkatan {{ $kls->angkatan }}
                            </small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-light text-dark">
                                {{ $kls->mahasiswa_count ?? $kls->mahasiswa->count() }} mhs
                            </span>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3">Belum ada kelas</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @endif
</div>

<style>
    .hover-lift { transition: all 0.3s ease; }
    .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 1rem 3rem rgba(0,0,0,0.175) !important; }
    .card { border-radius: 16px !important; }
    .card-header { border-radius: 16px 16px 0 0 !important; }
</style>
@endsection
