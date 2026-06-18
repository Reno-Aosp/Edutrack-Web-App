@extends('layouts.app')

@section('title', 'Detail Kelas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold" style="color:#5C1033;">
        Detail Kelas: {{ $kelas->nama_kelas }}
    </h5>
    <a href="{{ route('kelas.index') }}" class="btn btn-sm btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<!-- Info Kelas -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 text-center p-3" style="background:#FDE8F2;">
            <div class="small text-muted">Prodi</div>
            <div class="fw-bold" style="color:#5C1033;">{{ $kelas->prodi }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 text-center p-3" style="background:#FDE8F2;">
            <div class="small text-muted">Angkatan</div>
            <div class="fw-bold" style="color:#5C1033;">{{ $kelas->angkatan }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 text-center p-3" style="background:#FDE8F2;">
            <div class="small text-muted">Semester</div>
            <div class="fw-bold" style="color:#5C1033;">Semester {{ $kelas->semester }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 text-center p-3" style="background:#FDE8F2;">
            <div class="small text-muted">Total Mahasiswa</div>
            <div class="fw-bold" style="color:#E91E8C;">{{ $kelas->mahasiswa->count() }}</div>
        </div>
    </div>
</div>

<!-- Assign Mata Kuliah ke Kelas -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <h6 class="fw-bold mb-3" style="color:#5C1033;">Mata Kuliah di Kelas Ini</h6>
        <form method="POST" action="{{ route('kelas.assignMatkul', $kelas->id) }}">
            @csrf
            <div class="row g-2 mb-3">
                @foreach($semuaMataKuliah as $matkul)
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                            name="matkul_ids[]"
                            value="{{ $matkul->id }}"
                            id="matkul_{{ $matkul->id }}"
                            {{ $kelas->mataKuliah->contains($matkul->id) ? 'checked' : '' }}>
                        <label class="form-check-label" for="matkul_{{ $matkul->id }}">
                            <span class="badge me-1" style="background:#E91E8C;">{{ $matkul->kode }}</span>
                            {{ $matkul->nama }} ({{ $matkul->sks }} SKS)
                        </label>
                    </div>
                </div>
                @endforeach
            </div>
            <button type="submit" class="btn text-white fw-bold"
                style="background:#E91E8C;">
                <i class="bi bi-save"></i> Simpan Mata Kuliah
            </button>
        </form>
    </div>
</div>

<!-- Tambah Mahasiswa ke Kelas -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <h6 class="fw-bold mb-3" style="color:#5C1033;">Tambah Mahasiswa ke Kelas</h6>
        <form method="POST" action="{{ route('kelas.tambahMahasiswa', $kelas->id) }}">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-md-8">
                    <label class="form-label fw-bold small">Pilih Mahasiswa</label>
                    <select name="mahasiswa_ids[]" class="form-select" multiple>
                        @foreach($semuaMahasiswa as $mhs)
                            @if(!$kelas->mahasiswa->contains($mhs->id))
                                <option value="{{ $mhs->id }}">
                                    {{ $mhs->nama }} - {{ $mhs->nim }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    <small class="text-muted">Tahan Ctrl untuk pilih lebih dari satu</small>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn text-white fw-bold w-100"
                        style="background:#E91E8C;">
                        <i class="bi bi-plus-circle"></i> Tambahkan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Daftar Mahasiswa di Kelas -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <h6 class="fw-bold mb-3" style="color:#5C1033;">Daftar Mahasiswa</h6>
        <table class="table table-hover align-middle">
            <thead style="background:#FDE8F2;">
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>NIM</th>
                    <th>Prodi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kelas->mahasiswa as $i => $mhs)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $mhs->nama }}</td>
                    <td>{{ $mhs->nim }}</td>
                    <td>{{ $mhs->prodi }}</td>
                    <td>
                        <form action="{{ route('kelas.hapusMahasiswa', [$kelas->id, $mhs->id]) }}"
                            method="POST" class="d-inline"
                            onsubmit="return confirm('Hapus mahasiswa dari kelas ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        Belum ada mahasiswa di kelas ini
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection