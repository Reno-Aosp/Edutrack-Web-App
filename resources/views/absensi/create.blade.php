@extends('layouts.app')

@section('title', 'Input Absensi')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-0" style="color:#5C1033;">Input Absensi</h5>
                <small class="text-muted">{{ $kelas->nama_kelas }} · {{ $matkul->nama }}</small>
            </div>
            <a href="{{ route('absensi.index', ['kelas_id' => $kelas->id, 'matkul_id' => $matkul->id]) }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('absensi.store') }}">
            @csrf
            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
            <input type="hidden" name="matkul_id" value="{{ $matkul->id }}">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Mahasiswa</label>
                    <select name="mahasiswa_id" class="form-select" required>
                        <option value="">-- Pilih Mahasiswa --</option>
                        @foreach($mahasiswa as $mhs)
                        <option value="{{ $mhs->id }}">{{ $mhs->nama }} - {{ $mhs->nim }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control"
                        value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Status</label>
                    <div class="row g-2">
                        @foreach(['hadir' => 'success', 'sakit' => 'warning', 'izin' => 'primary', 'alpha' => 'danger'] as $status => $color)
                        <div class="col-6">
                            <div class="form-check border rounded-3 p-3">
                                <input class="form-check-input" type="radio"
                                    name="status" value="{{ $status }}"
                                    id="status_{{ $status }}"
                                    {{ $status == 'hadir' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-{{ $color }}" for="status_{{ $status }}">
                                    {{ ucfirst($status) }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Keterangan <span class="text-muted">(opsional)</span></label>
                    <textarea name="keterangan" class="form-control" rows="4"
                        placeholder="Contoh: Sakit demam, ada surat dokter"></textarea>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn text-white fw-bold" style="background:#E91E8C;">
                    <i class="bi bi-save"></i> Simpan Absensi
                </button>
                <a href="{{ route('absensi.index', ['kelas_id' => $kelas->id, 'matkul_id' => $matkul->id]) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection