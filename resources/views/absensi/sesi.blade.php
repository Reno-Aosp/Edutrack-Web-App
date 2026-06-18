@extends('layouts.app')
@section('title', 'Kelola Sesi Absensi')
@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Form Buka Sesi --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4" style="color:#5C1033;">Buka Sesi Absensi</h5>
        <form method="POST" action="{{ route('sesi-absensi.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Mata Kuliah</label>
                    <select name="matkul_id" class="form-select" required>
                        <option value="">Pilih Mata Kuliah</option>
                        @foreach($matkul as $m)
                            <option value="{{ $m->id }}">{{ $m->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Kelas</label>
                    <select name="kelas_id" class="form-select" required>
                        <option value="">Pilih Kelas</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control"
                        value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Jam Buka</label>
                    <input type="time" name="jam_buka" class="form-control"
                        value="{{ date('H:i') }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Jam Tutup</label>
                    <input type="time" name="jam_tutup" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Pertemuan Ke</label>
                    <input type="text" name="pertemuan_ke" class="form-control"
                        placeholder="Contoh: 5">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn text-white fw-bold"
                        style="background:#E91E8C;">
                        <i class="bi bi-door-open"></i> Buka Sesi Absensi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Daftar Sesi --}}
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4" style="color:#5C1033;">Daftar Sesi Absensi</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead style="background:#FFF0F7;">
                    <tr>
                        <th>Mata Kuliah</th>
                        <th>Kelas</th>
                        <th>Tanggal</th>
                        <th>Jam Buka</th>
                        <th>Jam Tutup</th>
                        <th>Pertemuan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sesi as $s)
                    <tr>
                        <td class="fw-bold">{{ $s->mataKuliah->nama ?? '-' }}</td>
                        <td>{{ $s->kelas->nama_kelas ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($s->tanggal)->format('d M Y') }}</td>
                        <td>{{ $s->jam_buka }}</td>
                        <td>{{ $s->jam_tutup ?? '-' }}</td>
                        <td>{{ $s->pertemuan_ke ? 'Ke-'.$s->pertemuan_ke : '-' }}</td>
                        <td>
                            @if($s->status == 'buka')
                                <span class="badge" style="background:#E91E8C;">
                                    <i class="bi bi-circle-fill me-1" style="font-size:8px;"></i>
                                    Buka
                                </span>
                            @else
                                <span class="badge bg-secondary">Tutup</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @if($s->status == 'buka')
                                <form method="POST"
                                    action="{{ route('sesi-absensi.tutup', $s->id) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-warning fw-bold">
                                        <i class="bi bi-door-closed"></i> Tutup
                                    </button>
                                </form>
                                @endif
                                <form method="POST"
                                    action="{{ route('sesi-absensi.destroy', $s->id) }}"
                                    onsubmit="return confirm('Hapus sesi ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
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
    </div>
</div>
@endsection