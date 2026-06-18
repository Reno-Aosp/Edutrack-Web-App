@extends('layouts.app')

@section('title', 'Tambah Jadwal')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4" style="color:#5C1033;">Form Tambah Jadwal</h5>
        <div class="mb-3 text-muted">
            Kelas: <strong>{{ $kelas->nama_kelas }}</strong> · {{ $kelas->prodi }}
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('jadwal.store') }}">
            @csrf
            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Mata Kuliah</label>
                    <select name="matkul_id" class="form-select" required>
                        <option value="">-- Pilih Mata Kuliah --</option>
                        @foreach($matkul as $mk)
                            <option value="{{ $mk->id }}">{{ $mk->nama }} ({{ $mk->kode }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Dosen</label>
                    <select name="dosen_id" class="form-select">
                        <option value="">-- Pilih Dosen (Opsional) --</option>
                        @foreach($dosen as $d)
                            <option value="{{ $d->id }}">{{ $d->user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Hari</label>
                    <select name="hari" class="form-select" required>
                        <option value="">-- Pilih Hari --</option>
                        @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $h)
                            <option value="{{ $h }}">{{ $h }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Jam Mulai</label>
                    <input type="time" name="jam_mulai" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Jam Selesai</label>
                    <input type="time" name="jam_selesai" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Ruangan</label>
                    <input type="text" name="ruangan" class="form-control" placeholder="Contoh: Lab A101">
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn text-white fw-bold" style="background:#E91E8C;">
                    <i class="bi bi-save"></i> Simpan
                </button>
                <a href="{{ route('jadwal.index', ['kelas_id' => $kelas->id]) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection