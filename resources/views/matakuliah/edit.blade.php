@extends('layouts.app')

@section('title', 'Edit Mata Kuliah')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4" style="color:#5C1033;">Form Edit Mata Kuliah</h5>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('matakuliah.update', $mataKuliah->id) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nama Mata Kuliah</label>
                    <input type="text" name="nama" class="form-control"
                        value="{{ $mataKuliah->nama }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Kode</label>
                    <input type="text" class="form-control"
                        value="{{ $mataKuliah->kode }}" disabled>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">SKS</label>
                    <select name="sks" class="form-select" required>
                        <option value="1" {{ $mataKuliah->sks == 1 ? 'selected' : '' }}>1 SKS</option>
                        <option value="2" {{ $mataKuliah->sks == 2 ? 'selected' : '' }}>2 SKS</option>
                        <option value="3" {{ $mataKuliah->sks == 3 ? 'selected' : '' }}>3 SKS</option>
                        <option value="4" {{ $mataKuliah->sks == 4 ? 'selected' : '' }}>4 SKS</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Dosen Pengampu</label>
                    <select name="dosen_id" class="form-select">
                        <option value="">-- Pilih Dosen (Opsional) --</option>
                        @foreach($dosen as $d)
                            <option value="{{ $d->id }}"
                                {{ $mataKuliah->dosen_id == $d->id ? 'selected' : '' }}>
                                {{ $d->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn text-white fw-bold"
                    style="background:#E91E8C;">
                    <i class="bi bi-save"></i> Update
                </button>
                <a href="{{ route('matakuliah.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection