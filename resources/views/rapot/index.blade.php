@extends('layouts.app')

@section('title', 'Rapor Akademik')

@section('content')
<div class="mb-4">
    <h5 class="fw-bold mb-3" style="color:#5C1033;">Rapor Akademik</h5>
    <small class="text-muted">Nilai dan prestasi akademik Anda</small>
</div>

@if(!$mahasiswa)
    {{-- Tampil dropdown untuk admin memilih kelas terlebih dahulu --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3">Pilih Kelas</h6>
            <form method="GET" action="{{ route('rapot.index') }}" class="row g-3">
                <div class="col-md-12">
                    <select name="kelas_id" class="form-select" onchange="this.form.submit()" required>
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($allKelas as $kls)
                            <option value="{{ $kls->id }}" {{ $kelasId == $kls->id ? 'selected' : '' }}>
                                {{ $kls->nama_kelas }} ({{ $kls->prodi }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    @if($kelasId && !$allMahasiswa->isEmpty())
    {{-- Tampil dropdown untuk memilih mahasiswa dari kelas yang dipilih --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3">Pilih Mahasiswa</h6>
            <form method="GET" action="{{ route('rapot.index') }}" class="row g-3">
                <div class="col-md-6">
                    <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
                    <select name="mahasiswa_id" class="form-select" required>
                        <option value="">-- Pilih Mahasiswa --</option>
                        @foreach($allMahasiswa as $mhs)
                            <option value="{{ $mhs->id }}">
                                {{ $mhs->nim }} - {{ $mhs->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn w-100" style="background:#E91E8C; color:#fff;">
                        <i class="bi bi-search"></i> Lihat Rapor
                    </button>
                </div>
            </form>
        </div>
    </div>
    @elseif($kelasId && $allMahasiswa->isEmpty())
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> Kelas ini tidak memiliki mahasiswa
    </div>
    @endif
@else
{{-- Kartu Informasi Mahasiswa --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4" style="background:#FDE8F2;">
            <div class="card-body">
                <small class="text-muted d-block mb-1">Nama</small>
                <strong>{{ $mahasiswa->user->name }}</strong>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4" style="background:#FDE8F2;">
            <div class="card-body">
                <small class="text-muted d-block mb-1">NIM</small>
                <strong>{{ $mahasiswa->nim }}</strong>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4" style="background:#FDE8F2;">
            <div class="card-body">
                <small class="text-muted d-block mb-1">Program Studi</small>
                <strong>{{ $mahasiswa->prodi }}</strong>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4" style="background:#FDE8F2;">
            <div class="card-body">
                <small class="text-muted d-block mb-1">Angkatan</small>
                <strong>{{ $mahasiswa->angkatan }}</strong>
            </div>
        </div>
    </div>
</div>

@if(Auth::user()->role === 'admin')
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <a href="{{ route('rapot.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Pilih Kelas
            </a>
        </div>
    </div>
@endif

{{-- Pilih Semester --}}
@if(empty($semuaSemester))
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> Belum ada data nilai untuk ditampilkan
    </div>
@else
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3">Pilih Semester</h6>
            <div class="row g-2">
                @foreach($semuaSemester as $sem)
                    <div class="col-auto">
                        @if(Auth::user()->role === 'admin')
                            <a href="{{ route('rapot.index', ['kelas_id' => $kelasId, 'mahasiswa_id' => $mahasiswa->id, 'semester' => $sem]) }}" 
                                class="btn btn-sm {{ $semester === $sem ? 'text-white' : 'btn-outline-secondary' }}"
                                style="{{ $semester === $sem ? 'background:#E91E8C;' : '' }}">
                                {{ $sem }}
                            </a>
                        @else
                            <a href="{{ route('rapot.index', ['semester' => $sem]) }}" 
                                class="btn btn-sm {{ $semester === $sem ? 'text-white' : 'btn-outline-secondary' }}"
                                style="{{ $semester === $sem ? 'background:#E91E8C;' : '' }}">
                                {{ $sem }}
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Tampil Data Rapor --}}
    @if($semester && !$nilaiData->isEmpty())
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body text-center">
                        <small class="text-muted d-block">IPK</small>
                        <h3 class="fw-bold" style="color:#E91E8C;">{{ $ipk }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body text-center">
                        <small class="text-muted d-block">Total SKS</small>
                        <h3 class="fw-bold" style="color:#E91E8C;">{{ $totalSks }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body text-center">
                        <small class="text-muted d-block">Mata Kuliah</small>
                        <h3 class="fw-bold" style="color:#E91E8C;">{{ $nilaiData->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel Nilai Detail --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Nilai Semester: <span class="text-muted">{{ $semester }}</span></h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead style="background:#FDE8F2;">
                            <tr>
                                <th>Kode</th>
                                <th>Mata Kuliah</th>
                                <th width="8%">SKS</th>
                                <th width="8%">Tugas</th>
                                <th width="8%">UTS</th>
                                <th width="8%">UAS</th>
                                <th width="10%">Nilai Akhir</th>
                                <th width="8%">Grade</th>
                                <th width="12%">Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($nilaiData as $nilai)
                            <tr>
                                <td>
                                    <small class="fw-bold">{{ $nilai['matkul_kode'] }}</small>
                                </td>
                                <td>
                                    <small>{{ $nilai['matkul_nama'] }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark">{{ $nilai['sks'] }}</span>
                                </td>
                                <td class="text-center">
                                    @if($nilai['nilai_tugas'] !== '-')
                                        {{ $nilai['nilai_tugas'] }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($nilai['nilai_uts'] !== '-')
                                        {{ $nilai['nilai_uts'] }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($nilai['nilai_uas'] !== '-')
                                        {{ $nilai['nilai_uas'] }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">
                                    @if($nilai['nilai_akhir'] !== '-')
                                        {{ round($nilai['nilai_akhir'], 1) }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $nilai['grade'] === 'A' ? 'success' : ($nilai['grade'] === 'B' ? 'info' : ($nilai['grade'] === 'C' ? 'warning' : 'danger')) }}">
                                        {{ $nilai['grade'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <small>
                                        {{ $nilai['hadir'] }}/{{ $nilai['total_pertemuan'] }}
                                        <br>
                                        <span class="badge bg-secondary">{{ $nilai['presensi_persen'] }}%</span>
                                    </small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Catatan --}}
        <div class="alert alert-info mt-4">
            <h6 class="fw-bold mb-2">Keterangan Nilai:</h6>
            <div class="row g-3 small">
                <div class="col-md-3">
                    <strong>A:</strong> 85-100 (Sangat Baik)
                </div>
                <div class="col-md-3">
                    <strong>B:</strong> 75-84 (Baik)
                </div>
                <div class="col-md-3">
                    <strong>C:</strong> 65-74 (Cukup)
                </div>
                <div class="col-md-3">
                    <strong>D/E:</strong> < 65 (Kurang)
                </div>
            </div>
        </div>
    @elseif($semester)
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i> Belum ada data nilai untuk semester {{ $semester }}
        </div>
    @endif
@endif
@endif
@endsection
