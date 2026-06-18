@extends('layouts.app')
@section('title', 'Data Absensi')
@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Step 1: Pilih Kelas --}}
@if(!isset($kelas))
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0" style="color:#5C1033;">Pilih Kelas</h5>
    </div>
    <div class="row g-3">
        @forelse($semuaKelas as $k)
        <div class="col-md-4">
            <a href="{{ route('absensi.index', ['kelas_id' => $k->id]) }}"
                class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                    <div class="fw-bold fs-5" style="color:#5C1033;">{{ $k->nama_kelas }}</div>
                    <div class="text-muted small">{{ $k->prodi }}</div>
                    <div class="text-muted small">Angkatan {{ $k->angkatan }}</div>
                    <div class="mt-2">
                        <span class="badge" style="background:#E91E8C;">
                            {{ $k->mahasiswa_count }} Mahasiswa
                        </span>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-warning">Belum ada kelas.</div>
        </div>
        @endforelse
    </div>

{{-- Step 2: Pilih Mata Kuliah --}}
@elseif(!isset($matkul))
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0" style="color:#5C1033;">Pilih Mata Kuliah</h5>
            <small class="text-muted">Kelas {{ $kelas->nama_kelas }}</small>
        </div>
        <a href="{{ route('absensi.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="row g-3">
        @forelse($semuaMatkul as $mk)
        <div class="col-md-4">
            <a href="{{ route('absensi.index', ['kelas_id' => $kelas->id, 'matkul_id' => $mk->id]) }}"
                class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                    <div class="fw-bold fs-5" style="color:#5C1033;">{{ $mk->nama }}</div>
                    <div class="text-muted small">{{ $mk->kode }} · {{ $mk->sks }} SKS</div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-warning">Belum ada mata kuliah untuk kelas ini.</div>
        </div>
        @endforelse
    </div>

{{-- Step 3: Tampil Absensi --}}
@else
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0" style="color:#5C1033;">
                Absensi · {{ $kelas->nama_kelas }} · {{ $matkul->nama }}
            </h5>
            <small class="text-muted">{{ $kelas->prodi }}</small>
        </div>
        <a href="{{ route('absensi.index', ['kelas_id' => $kelas->id]) }}"
            class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- Status sesi aktif --}}
    @if(isset($sesiAktif) && $sesiAktif)
        <div class="alert d-flex align-items-center gap-2 mb-3"
            style="background:#E91E8C; color:white; border:none;">
            <i class="bi bi-circle-fill" style="font-size:10px;"></i>
            <strong>Sesi Sedang Dibuka</strong> —
            Pertemuan ke-{{ $sesiAktif->pertemuan_ke ?? '-' }} |
            Jam: {{ $sesiAktif->jam_buka }} s/d {{ $sesiAktif->jam_tutup }}
            <span class="ms-2 badge bg-white text-danger">
                Mahasiswa bisa absen via Flutter
            </span>
        </div>
    @endif

    {{-- Rekap --}}
    @php
        $hadir = $absensi->where('status', 'hadir')->count();
        $sakit = $absensi->where('status', 'sakit')->count();
        $izin  = $absensi->where('status', 'izin')->count();
        $alpha = $absensi->where('status', 'alpha')->count();
    @endphp
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card border-0 rounded-4 text-center p-3" style="background:#d4edda;">
                <div class="fw-bold fs-4 text-success">{{ $hadir }}</div>
                <div class="small text-success">Hadir</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 rounded-4 text-center p-3" style="background:#fff3cd;">
                <div class="fw-bold fs-4 text-warning">{{ $sakit }}</div>
                <div class="small text-warning">Sakit</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 rounded-4 text-center p-3" style="background:#cce5ff;">
                <div class="fw-bold fs-4 text-primary">{{ $izin }}</div>
                <div class="small text-primary">Izin</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 rounded-4 text-center p-3" style="background:#f8d7da;">
                <div class="fw-bold fs-4 text-danger">{{ $alpha }}</div>
                <div class="small text-danger">Alpha</div>
            </div>
        </div>
    </div>

    {{-- Tabel absensi --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0" style="color:#5C1033;">Daftar Absensi</h6>
                <div class="d-flex align-items-center gap-2">
                    @if(isset($sesiAktif) && $sesiAktif)
                        <span class="text-muted small" id="lastRefresh">
                            Auto-refresh aktif
                        </span>
                    @endif
                    <a href="{{ request()->fullUrl() }}"
                        class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </a>
                </div>
            </div>
            <table class="table table-hover align-middle" id="tabelAbsensi">
                <thead style="background:#FDE8F2;">
                    <tr>
                        <th>No</th>
                        <th>Mahasiswa</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th>Surat</th>  {{-- ← KOLOM BARU --}}
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absensi as $i => $a)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $a->mahasiswa->nama ?? '-' }}</td>
                        <td>{{ $a->tanggal }}</td>
                        <td>
                            @php
                                $badge = match($a->status) {
                                    'hadir' => 'success',
                                    'sakit' => 'warning',
                                    'izin'  => 'primary',
                                    'alpha' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $badge }}">
                                {{ ucfirst($a->status) }}
                            </span>
                        </td>
                        <td>{{ $a->keterangan ?? '-' }}</td>

                        {{-- ← KOLOM SURAT (foto dari Supabase Storage) --}}
                        <td>
                            @if($a->foto_url)
                                <a href="{{ $a->foto_url }}" target="_blank"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-image"></i> Lihat Surat
                                </a>
                            @else
                                @if(in_array($a->status, ['sakit', 'izin']))
                                    <span class="badge bg-warning text-dark">Belum upload</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('absensi.edit', $a->id) }}"
                                class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('absensi.destroy', $a->id) }}"
                                method="POST" class="d-inline"
                                onsubmit="return confirm('Hapus absensi ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyRow">
                        <td colspan="7" class="text-center text-muted">
                            Belum ada data absensi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Auto-refresh setiap 10 detik kalau ada sesi aktif --}}
    @if(isset($sesiAktif) && $sesiAktif)
    <script>
        let countdown = 10;
        const lastRefreshEl = document.getElementById('lastRefresh');

        setInterval(() => {
            countdown--;
            if (lastRefreshEl) {
                lastRefreshEl.textContent = `Refresh dalam ${countdown}s`;
            }
            if (countdown <= 0) {
                window.location.reload();
            }
        }, 1000);
    </script>
    @endif
@endif

@endsection