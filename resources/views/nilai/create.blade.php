@extends('layouts.app')

@section('title', 'Input Nilai Mahasiswa')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="mb-4">
            <h5 class="fw-bold mb-1" style="color:#5C1033;">Input Nilai Mahasiswa</h5>
            <small class="text-muted">
                <strong>{{ $kelas->nama_kelas }}</strong> · {{ $kelas->prodi }} · Angkatan {{ $kelas->angkatan }}<br>
                <strong>{{ $matkul->nama }}</strong> ({{ $matkul->kode }}) · <strong>{{ $semester }}</strong>
            </small>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('nilai.store') }}">
            @csrf
            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
            <input type="hidden" name="matkul_id" value="{{ $matkul->id }}">
            <input type="hidden" name="semester" value="{{ $semester }}">

            @if($mahasiswa->isEmpty())
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Tidak ada mahasiswa di kelas ini
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead style="background:#FDE8F2;">
                            <tr>
                                <th width="25%">Nama Mahasiswa</th>
                                <th width="12%">NIM</th>
                                <th width="12%">Tugas</th>
                                <th width="12%">UTS</th>
                                <th width="12%">UAS</th>
                                <th width="12%">Rata-rata</th>
                                <th width="15%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mahasiswa as $mhs)
                            @php
                                $existingNilai = $mhs->nilai()->where('matkul_id', $matkul->id)
                                    ->where('kelas_id', $kelas->id)
                                    ->where('semester', $semester)
                                    ->first();
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $mhs->user->name }}</strong><br>
                                    <small class="text-muted">{{ $mhs->user->email }}</small>
                                </td>
                                <td>{{ $mhs->nim }}</td>
                                <td>
                                    <input type="number" name="tugas[{{ $mhs->id }}]" 
                                        class="form-control form-control-sm"
                                        min="0" max="100" step="0.5"
                                        value="{{ $existingNilai->nilai_tugas ?? '' }}"
                                        placeholder="0">
                                </td>
                                <td>
                                    <input type="number" name="uts[{{ $mhs->id }}]" 
                                        class="form-control form-control-sm"
                                        min="0" max="100" step="0.5"
                                        value="{{ $existingNilai->nilai_uts ?? '' }}"
                                        placeholder="0">
                                </td>
                                <td>
                                    <input type="number" name="uas[{{ $mhs->id }}]" 
                                        class="form-control form-control-sm"
                                        min="0" max="100" step="0.5"
                                        value="{{ $existingNilai->nilai_uas ?? '' }}"
                                        placeholder="0">
                                </td>
                                <td class="fw-bold text-center">
                                    @if($existingNilai && ($existingNilai->nilai_tugas || $existingNilai->nilai_uts || $existingNilai->nilai_uas))
                                        <span class="badge bg-info">
                                            {{ round(($existingNilai->nilai_tugas + $existingNilai->nilai_uts + $existingNilai->nilai_uas) / 3, 1) }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($existingNilai)
                                        <span class="badge bg-success">Ada</span>
                                    @else
                                        <span class="text-muted small">Baru</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn text-white fw-bold"
                    style="background:#E91E8C;" @if($mahasiswa->isEmpty()) disabled @endif>
                    <i class="bi bi-save"></i> Simpan Nilai
                </button>
                <a href="{{ url()->previous() }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection