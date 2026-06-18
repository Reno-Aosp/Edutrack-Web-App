@extends('layouts.app')

@section('title', 'Edit Nilai')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4" style="color:#5C1033;">Form Edit Nilai</h5>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('nilai.update', $nilai->id) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Mahasiswa</label>
                    <select name="mahasiswa_id" class="form-select" required>
                        @foreach($mahasiswa as $mhs)
                            <option value="{{ $mhs->id }}"
                                {{ $nilai->mahasiswa_id == $mhs->id ? 'selected' : '' }}>
                                {{ $mhs->user->name }} - {{ $mhs->nim }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Mata Kuliah</label>
                    <select name="matkul_id" class="form-select" required>
                        @foreach($mataKuliah as $mk)
                            <option value="{{ $mk->id }}"
                                {{ $nilai->matkul_id == $mk->id ? 'selected' : '' }}>
                                {{ $mk->nama }} ({{ $mk->kode }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nilai Tugas</label>
                    <input type="number" name="nilai_tugas" class="form-control"
                        min="0" max="100" value="{{ $nilai->nilai_tugas }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nilai UTS</label>
                    <input type="number" name="nilai_uts" class="form-control"
                        min="0" max="100" value="{{ $nilai->nilai_uts }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nilai UAS</label>
                    <input type="number" name="nilai_uas" class="form-control"
                        min="0" max="100" value="{{ $nilai->nilai_uas }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Semester</label>
                    <select name="semester" class="form-select" required>
                        <option value="Ganjil 2024/2025" {{ $nilai->semester == 'Ganjil 2024/2025' ? 'selected' : '' }}>Ganjil 2024/2025</option>
                        <option value="Genap 2024/2025" {{ $nilai->semester == 'Genap 2024/2025' ? 'selected' : '' }}>Genap 2024/2025</option>
                        <option value="Ganjil 2025/2026" {{ $nilai->semester == 'Ganjil 2025/2026' ? 'selected' : '' }}>Ganjil 2025/2026</option>
                        <option value="Genap 2025/2026" {{ $nilai->semester == 'Genap 2025/2026' ? 'selected' : '' }}>Genap 2025/2026</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn text-white fw-bold"
                    style="background:#E91E8C;">
                    <i class="bi bi-save"></i> Update
                </button>
                <a href="{{ route('nilai.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection