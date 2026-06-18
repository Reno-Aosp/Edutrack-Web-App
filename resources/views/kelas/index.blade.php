@extends('layouts.app')

@section('title', 'Data Kelas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold" style="color:#5C1033;">Daftar Kelas</h5>
    <a href="{{ route('kelas.create') }}" class="btn btn-sm text-white"
        style="background:#E91E8C;">
        <i class="bi bi-plus-circle"></i> Tambah Kelas
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead style="background:#FDE8F2;">
                <tr>
                    <th>No</th>
                    <th>Nama Kelas</th>
                    <th>Prodi</th>
                    <th>Angkatan</th>
                    <th>Semester</th>
                    <th>Jumlah Mahasiswa</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kelas as $i => $k)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $k->nama_kelas }}</td>
                    <td>{{ $k->prodi }}</td>
                    <td>{{ $k->angkatan }}</td>
                    <td>Semester {{ $k->semester }}</td>
                    <td>
                        <span class="badge" style="background:#E91E8C;">
                            {{ $k->mahasiswa_count }} Mahasiswa
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('kelas.show', $k->id) }}"
                            class="btn btn-sm btn-info text-white">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('kelas.edit', $k->id) }}"
                            class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('kelas.destroy', $k->id) }}"
                            method="POST" class="d-inline"
                            onsubmit="return confirm('Hapus kelas ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        Belum ada data kelas
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection