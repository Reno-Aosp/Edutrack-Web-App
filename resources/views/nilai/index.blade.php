@extends('layouts.app')

@section('title', 'Data Nilai')

@section('content')

@if(!isset($kelas))
    <h5 class="fw-bold mb-3" style="color:#5C1033;">Pilih Kelas</h5>
    <div class="row g-3">
        @forelse($semuaKelas as $k)
        <div class="col-md-4">
            <a href="{{ route('nilai.index', ['kelas_id' => $k->id]) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                    <div class="fw-bold fs-5" style="color:#5C1033;">{{ $k->nama_kelas }}</div>
                    <div class="text-muted small">{{ $k->prodi }}</div>
                    <div class="text-muted small">Angkatan {{ $k->angkatan }} · Semester {{ $k->semester }}</div>
                    <div class="mt-2">
                        <span class="badge" style="background:#E91E8C;">{{ $k->mahasiswa_count }} Mahasiswa</span>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-warning">
                Belum ada kelas. <a href="{{ route('kelas.create') }}">Tambah kelas dulu</a>
            </div>
        </div>
        @endforelse
    </div>

@else
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0" style="color:#5C1033;">Nilai - {{ $kelas->nama_kelas }}</h5>
            <small class="text-muted">{{ $kelas->prodi }} · Semester {{ $kelas->semester }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('nilai.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('nilai.create', ['kelas_id' => $kelas->id]) }}" class="btn btn-sm text-white" style="background:#E91E8C;">
                <i class="bi bi-plus-circle"></i> Input Nilai
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead style="background:#FDE8F2;">
                    <tr>
                        <th>No</th>
                        <th>Mahasiswa</th>
                        <th>Mata Kuliah</th>
                        <th>Tugas</th>
                        <th>UTS</th>
                        <th>UAS</th>
                        <th>Nilai Akhir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nilai as $i => $n)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $n->mahasiswa->nama ?? '-' }}</td>
                        <td>{{ $n->mataKuliah->nama ?? '-' }}</td>
                        <td>{{ $n->nilai_tugas }}</td>
                        <td>{{ $n->nilai_uts }}</td>
                        <td>{{ $n->nilai_uas }}</td>
                        <td>
                            @php $na = $n->nilai_akhir; @endphp
                            <span class="badge {{ $na >= 60 ? 'bg-success' : 'bg-danger' }}">{{ $na }}</span>
                        </td>
                        <td>
                            <a href="{{ route('nilai.edit', $n->id) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('nilai.destroy', $n->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus nilai ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">Belum ada data nilai di kelas ini</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection