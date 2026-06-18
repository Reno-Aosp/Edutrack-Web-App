@extends('layouts.app')

@section('title', 'Tambah Kelas')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4" style="color:#5C1033;">Form Tambah Kelas</h5>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('kelas.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nama Kelas</label>
                    <input type="text" name="nama_kelas" class="form-control"
                        placeholder="Contoh: TI-2A" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Program Studi</label>
                    <input type="text" name="prodi" class="form-control"
                        placeholder="Contoh: Teknik Informatika" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Angkatan</label>
                    <input type="number" name="angkatan" class="form-control"
                        placeholder="Contoh: 2024" min="2000" max="2099" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Semester</label>
                    <select name="semester" class="form-select" required>
                        <option value="">-- Pilih Semester --</option>
                        @for($i = 1; $i <= 14; $i++)
                            <option value="{{ $i }}">Semester {{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn text-white fw-bold"
                    style="background:#E91E8C;">
                    <i class="bi bi-save"></i> Simpan
                </button>
                <a href="{{ route('kelas.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection