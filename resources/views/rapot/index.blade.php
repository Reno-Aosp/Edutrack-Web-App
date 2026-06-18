@extends('layouts.app')

@section('title', 'Rapor Akademik')

@section('content')
<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:#5C1033;">Rapor Akademik</h5>
    <small class="text-muted">
        @if($isDosen ?? false)
            Rapor mahasiswa di kelas yang Anda ampu
        @else
            Nilai dan prestasi akademik mahasiswa
        @endif
    </small>
</div>

@if(!$mahasiswa)
    {{-- ── Pilih Kelas ── --}}
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
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3">Pilih Mahasiswa</h6>
            <form method="GET" action="{{ route('rapot.index') }}" class="row g-3">
                <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
                <div class="col-md-6">
                    <select name="mahasiswa_id" class="form-select" required>
                        <option value="">-- Pilih Mahasiswa --</option>
                        @foreach($allMahasiswa as $mhs)
                            <option value="{{ $mhs->id }}">
                                {{ $mhs->nim }} - {{ $mhs->user->name ?? $mhs->nama }}
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
    {{-- ── Info Mahasiswa ── --}}
    <div class="row g-3 mb-4">
        @foreach([
            ['Nama',          $mahasiswa->user->name ?? $mahasiswa->nama],
            ['NIM',           $mahasiswa->nim],
            ['Program Studi', $mahasiswa->prodi],
            ['Angkatan',      $mahasiswa->angkatan],
        ] as [$label, $val])
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4" style="background:#FDE8F2;">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">{{ $label }}</small>
                    <strong>{{ $val }}</strong>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <a href="{{ route('rapot.index', ['kelas_id' => $kelasId]) }}"
                class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Pilih Mahasiswa
            </a>
        </div>
    </div>

    {{-- ── Pilih Semester ── --}}
    @if(empty($semuaSemester))
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Belum ada data nilai
            @if($isDosen ?? false) untuk mata kuliah yang Anda ampu @endif
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">Pilih Semester</h6>
                <div class="row g-2">
                    @foreach($semuaSemester as $sem)
                    <div class="col-auto">
                        <a href="{{ route('rapot.index', [
                                'kelas_id'     => $kelasId,
                                'mahasiswa_id' => $mahasiswa->id,
                                'semester'     => $sem,
                           ]) }}"
                            class="btn btn-sm {{ $semester === $sem ? 'text-white' : 'btn-outline-secondary' }}"
                            style="{{ $semester === $sem ? 'background:#E91E8C;' : '' }}">
                            {{ $sem }}
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        @if($semester && !$nilaiData->isEmpty())
            {{-- Summary --}}
            <div class="row g-3 mb-4">
                @foreach([
                    ['IPK',          $ipk,              '#E91E8C'],
                    ['Total SKS',    $totalSks,         '#E91E8C'],
                    ['Mata Kuliah',  $nilaiData->count(),'#E91E8C'],
                ] as [$lbl, $val, $clr])
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body text-center">
                            <small class="text-muted d-block">{{ $lbl }}</small>
                            <h3 class="fw-bold" style="color:{{ $clr }};">{{ $val }}</h3>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Tabel Nilai --}}
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        Nilai Semester: <span class="text-muted">{{ $semester }}</span>
                        @if($isDosen ?? false)
                            <small class="text-muted ms-2">(Hanya mata kuliah yang Anda ampu)</small>
                        @endif
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead style="background:#FDE8F2;">
                                <tr>
                                    <th>Kode</th>
                                    <th>Mata Kuliah</th>
                                    <th width="6%">SKS</th>
                                    <th width="8%">Tugas</th>
                                    <th width="8%">UTS</th>
                                    <th width="8%">UAS</th>
                                    <th width="10%">Nilai Akhir</th>
                                    <th width="7%">Grade</th>
                                    <th width="12%">Kehadiran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nilaiData as $nilai)
                                <tr>
                                    <td><small class="fw-bold">{{ $nilai['matkul_kode'] }}</small></td>
                                    <td><small>{{ $nilai['matkul_nama'] }}</small></td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark">{{ $nilai['sks'] }}</span>
                                    </td>
                                    <td class="text-center">{{ $nilai['nilai_tugas'] !== '-' ? $nilai['nilai_tugas'] : '-' }}</td>
                                    <td class="text-center">{{ $nilai['nilai_uts'] !== '-' ? $nilai['nilai_uts'] : '-' }}</td>
                                    <td class="text-center">{{ $nilai['nilai_uas'] !== '-' ? $nilai['nilai_uas'] : '-' }}</td>
                                    <td class="text-center fw-bold">
                                        {{ $nilai['nilai_akhir'] !== '-' ? round($nilai['nilai_akhir'], 1) : '-' }}
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $gc = match($nilai['grade']) {
                                                'A' => 'success', 'B' => 'info',
                                                'C' => 'warning', default => 'danger'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $gc }}">{{ $nilai['grade'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <small>
                                            {{ $nilai['hadir'] }}/{{ $nilai['total_pertemuan'] }}<br>
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

            <div class="alert alert-info mt-4">
                <h6 class="fw-bold mb-2">Keterangan Grade:</h6>
                <div class="row g-3 small">
                    <div class="col-md-3"><strong>A:</strong> 85-100 (Sangat Baik)</div>
                    <div class="col-md-3"><strong>B:</strong> 75-84 (Baik)</div>
                    <div class="col-md-3"><strong>C:</strong> 65-74 (Cukup)</div>
                    <div class="col-md-3"><strong>D/E:</strong> &lt; 65 (Kurang)</div>
                </div>
            </div>

        @elseif($semester)
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                Belum ada data nilai untuk semester {{ $semester }}
            </div>
        @endif
    @endif
@endif
@endsection
