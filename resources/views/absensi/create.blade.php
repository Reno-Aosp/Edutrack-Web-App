@extends('layouts.app')

@section('title', 'Input Absensi')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4" style="color:#5C1033;">Form Input Absensi</h5>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('absensi.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Mahasiswa</label>
                    <select name="mahasiswa_id" class="form-select" required>
                        <option value="">-- Pilih Mahasiswa --</option>
                        @foreach($mahasiswa as $mhs)
                            <option value="{{ $mhs->id }}">
                                {{ $mhs->user->name }} - {{ $mhs->nim }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Mata Kuliah</label>
                    <select name="matkul_id" class="form-select" required>
                        <option value="">-- Pilih Mata Kuliah --</option>
                        @foreach($mataKuliah as $mk)
                            <option value="{{ $mk->id }}">
                                {{ $mk->nama }} ({{ $mk->kode }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="">-- Pilih Status --</option>
                        <option value="hadir">Hadir</option>
                        <option value="sakit">Sakit</option>
                        <option value="izin">Izin</option>
                        <option value="alpha">Alpha</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="3"
                        placeholder="Opsional..."></textarea>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn text-white fw-bold"
                    style="background:#E91E8C;">
                    <i class="bi bi-save"></i> Simpan
                </button>
                <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection