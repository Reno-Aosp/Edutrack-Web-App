@extends('layouts.app')

@section('title', 'Tambah Mata Kuliah')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4" style="color:#5C1033;">Form Tambah Mata Kuliah</h5>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('matakuliah.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nama Mata Kuliah</label>
                    <input type="text" name="nama" class="form-control"
                        placeholder="Contoh: Pemrograman Web" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Kode</label>
                    <input type="text" name="kode" class="form-control"
                        placeholder="Contoh: TI301" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">SKS</label>
                    <select name="sks" class="form-select" required>
                        <option value="">-- Pilih SKS --</option>
                        <option value="1">1 SKS</option>
                        <option value="2">2 SKS</option>
                        <option value="3">3 SKS</option>
                        <option value="4">4 SKS</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Dosen Pengampu</label>
                    <select name="dosen_id" class="form-select">
                        <option value="">-- Pilih Dosen (Opsional) --</option>
                        @foreach($dosen as $d)
                            <option value="{{ $d->id }}">{{ $d->user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn text-white fw-bold"
                    style="background:#E91E8C;">
                    <i class="bi bi-save"></i> Simpan
                </button>
                <a href="{{ route('matakuliah.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection