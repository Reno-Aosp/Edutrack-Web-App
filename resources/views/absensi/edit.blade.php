@extends('layouts.app')

@section('title', 'Edit Absensi')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4" style="color:#5C1033;">Form Edit Absensi</h5>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('absensi.update', $absensi->id) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Mahasiswa</label>
                    <select name="mahasiswa_id" class="form-select" required>
                        @foreach($mahasiswa as $mhs)
                            <option value="{{ $mhs->id }}"
                                {{ $absensi->mahasiswa_id == $mhs->id ? 'selected' : '' }}>
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
                                {{ $absensi->matkul_id == $mk->id ? 'selected' : '' }}>
                                {{ $mk->nama }} ({{ $mk->kode }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control"
                        value="{{ $absensi->tanggal }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="hadir" {{ $absensi->status == 'hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="sakit" {{ $absensi->status == 'sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="izin" {{ $absensi->status == 'izin' ? 'selected' : '' }}>Izin</option>
                        <option value="alpha" {{ $absensi->status == 'alpha' ? 'selected' : '' }}>Alpha</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="3">{{ $absensi->keterangan }}</textarea>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn text-white fw-bold"
                    style="background:#E91E8C;">
                    <i class="bi bi-save"></i> Update
                </button>
                <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
```

Tekan **Ctrl+S**.

---

Semua Views sudah selesai! ✅ Sekarang coba buka browser dan ketik:
```
http://127.0.0.1:8000