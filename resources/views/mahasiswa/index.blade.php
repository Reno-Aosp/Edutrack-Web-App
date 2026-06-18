@extends('layouts.app')

@section('title', 'Data Mahasiswa')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold" style="color:#5C1033;">Daftar Mahasiswa</h5>
    <a href="{{ route('mahasiswa.create') }}" class="btn btn-sm text-white"
        style="background:#E91E8C;">
        <i class="bi bi-plus-circle"></i> Tambah Mahasiswa
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead style="background:#FDE8F2;">
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>NIM</th>
                    <th>Prodi</th>
                    <th>Angkatan</th>
                    <th>Kelas</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mahasiswa as $i => $mhs)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $mhs->nama }}</td>
                    <td>{{ $mhs->nim }}</td>
                    <td>{{ $mhs->prodi }}</td>
                    <td>{{ $mhs->angkatan }}</td>
                    <td>
                        @forelse($mhs->kelas as $k)
                            <span class="badge" style="background:#E91E8C;">{{ $k->nama_kelas }}</span>
                        @empty
                            <span class="text-muted small">Belum ada kelas</span>
                        @endforelse
                    </td>
                    <td>
                        <a href="{{ route('mahasiswa.edit', $mhs->id) }}"
                            class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('mahasiswa.destroy', $mhs->id) }}"
                            method="POST" class="d-inline"
                            onsubmit="return confirm('Hapus mahasiswa ini?')">
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
                        Belum ada data mahasiswa
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection