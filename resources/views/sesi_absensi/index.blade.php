@extends('layouts.app')
@section('title', 'Kelola Sesi Absensi')
@section('content')

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

{{-- ================= FORM BUKA SESI (FIX) ================= --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-3" style="color:#5C1033;">Buka Sesi Absensi</h5>

        <form method="POST" action="{{ route('sesi-absensi.store') }}">
            @csrf

            <div class="row g-3 align-items-end">

                {{-- Mata Kuliah --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold">Mata Kuliah</label>
                    <select name="matkul_id" class="form-select" required>
                        <option value="">-- Pilih Mata Kuliah --</option>
                        @foreach($matkul as $m)
                            <option value="{{ $m->id }}">{{ $m->nama }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Kelas --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold">Kelas</label>
                    <select name="kelas_id" class="form-select" required>
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Jam Buka --}}
                <div class="col-md-2">
                    <label class="form-label fw-bold">Jam Buka</label>
                    <input type="time" name="jam_buka" class="form-control"
                        value="{{ date('H:i') }}" required>
                </div>

                {{-- Jam Tutup (WAJIB) --}}
                <div class="col-md-2">
                    <label class="form-label fw-bold">
                        Jam Tutup <span class="text-danger">*</span>
                    </label>
                    <input type="time" name="jam_tutup" class="form-control" required>
                    <small class="text-muted">Wajib diisi</small>
                </div>

                {{-- Pertemuan --}}
                <div class="col-md-1">
                    <label class="form-label fw-bold">Pertemuan</label>
                    <input type="number" name="pertemuan_ke" class="form-control"
                        placeholder="1" min="1">
                </div>

                {{-- Button --}}
                <div class="col-md-1">
                    <button type="submit" class="btn text-white fw-bold w-100"
                        style="background:#E91E8C;">
                        <i class="bi bi-unlock"></i> Buka
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ================= TABEL SESI ================= --}}
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-3" style="color:#5C1033;">
            Riwayat Sesi Absensi
        </h5>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead style="background:#FFF0F7;">
                    <tr>
                        <th>Tanggal</th>
                        <th>Mata Kuliah</th>
                        <th>Kelas</th>
                        <th>Pertemuan</th>
                        <th>Jam Buka</th>
                        <th>Jam Tutup</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($sesi as $s)
                    <tr>
                        <td>
                            {{ \Carbon\Carbon::parse($s->tanggal)->format('d M Y') }}
                        </td>

                        <td class="fw-bold">
                            {{ $s->mataKuliah->nama ?? '-' }}
                        </td>

                        <td>
                            {{ $s->kelas->nama_kelas ?? '-' }}
                        </td>

                        <td>
                            {{ $s->pertemuan_ke ? 'Ke-'.$s->pertemuan_ke : '-' }}
                        </td>

                        <td>{{ $s->jam_buka ?? '-' }}</td>
                        <td>{{ $s->jam_tutup ?? '-' }}</td>

                        {{-- Status --}}
                        <td>
                            @if($s->status == 'buka')
                                <span class="badge rounded-pill" style="background:#E91E8C;">
                                    <i class="bi bi-unlock"></i> Dibuka
                                </span>
                            @else
                                <span class="badge bg-secondary rounded-pill">
                                    <i class="bi bi-lock"></i> Ditutup
                                </span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="d-flex gap-1">

                            @if($s->status == 'buka')
                            <form method="POST" action="{{ route('sesi-absensi.tutup', $s->id) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm btn-warning fw-bold">
                                    <i class="bi bi-lock"></i> Tutup
                                </button>
                            </form>
                            @endif

                            <form method="POST" action="{{ route('sesi-absensi.destroy', $s->id) }}"
                                onsubmit="return confirm('Hapus sesi ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>

                        </td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Belum ada sesi absensi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        {{ $sesi->links() }}

    </div>
</div>

@endsection