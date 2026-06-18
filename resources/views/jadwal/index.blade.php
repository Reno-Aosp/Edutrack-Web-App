@extends('layouts.app')

@section('title', 'Jadwal Kuliah')

@section('content')

@if(!isset($kelas))
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0" style="color:#5C1033;">Pilih Kelas</h5>
    </div>
    <div class="row g-3">
        @forelse($semuaKelas as $k)
        <div class="col-md-4">
            <a href="{{ route('jadwal.index', ['kelas_id' => $k->id]) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                    <div class="fw-bold fs-5" style="color:#5C1033;">{{ $k->nama_kelas }}</div>
                    <div class="text-muted small">{{ $k->prodi }}</div>
                    <div class="text-muted small">Angkatan {{ $k->angkatan }} · Semester {{ $k->semester }}</div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-warning">Belum ada kelas.</div>
        </div>
        @endforelse
    </div>

@else
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0" style="color:#5C1033;">Jadwal - {{ $kelas->nama_kelas }}</h5>
            <small class="text-muted">{{ $kelas->prodi }} · Angkatan {{ $kelas->angkatan }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('jadwal.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            {{-- Dosen bisa tambah jadwal untuk matkulnya sendiri --}}
            <a href="{{ route('jadwal.create', ['kelas_id' => $kelas->id]) }}"
               class="btn btn-sm text-white" style="background:#E91E8C;">
                <i class="bi bi-plus-circle"></i> Tambah Jadwal
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($isDosen ?? false)
    <div class="alert alert-info mb-3">
        <i class="bi bi-info-circle"></i>
        Menampilkan jadwal untuk mata kuliah yang Anda ampu di kelas ini.
    </div>
    @endif

    @php $hariUrut = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu']; @endphp

    @foreach($hariUrut as $hari)
        @php $jadwalHari = $jadwal->where('hari', $hari); @endphp
        @if($jadwalHari->count() > 0)
        <div class="mb-3">
            <div class="fw-bold mb-2 px-1" style="color:#E91E8C;">
                <i class="bi bi-calendar-day me-1"></i>{{ $hari }}
            </div>
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background:#FDE8F2;">
                            <tr>
                                <th class="ps-3">Jam</th>
                                <th>Mata Kuliah</th>
                                <th>Ruangan</th>
                                <th>Dosen</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jadwalHari as $j)
                            <tr>
                                <td class="ps-3">
                                    {{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($j->jam_selesai)->format('H:i') }}
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $j->mataKuliah->nama ?? '-' }}</div>
                                    <small class="text-muted">{{ $j->mataKuliah->kode ?? '' }}</small>
                                </td>
                                <td>{{ $j->ruangan ?? '-' }}</td>
                                <td>{{ $j->dosen->user->name ?? '-' }}</td>
                                <td>
                                    {{-- Dosen hanya bisa edit/hapus jadwal matkulnya sendiri --}}
                                    @if(Auth::user()->role === 'admin' ||
                                        ($j->mataKuliah && Auth::user()->dosen &&
                                         $j->mataKuliah->dosen_id == Auth::user()->dosen->id))
                                    <a href="{{ route('jadwal.edit', $j->id) }}"
                                       class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('jadwal.destroy', $j->id) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Hapus jadwal ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @else
                                    <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    @endforeach

    @if($jadwal->count() === 0)
        <div class="alert alert-warning">
            Belum ada jadwal
            @if($isDosen ?? false) untuk mata kuliah yang Anda ampu @endif
            di kelas ini.
        </div>
    @endif
@endif

@endsection
