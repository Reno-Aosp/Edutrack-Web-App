@extends('layouts.app')

@section('title', 'Data Mata Kuliah')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold" style="color:#5C1033;">Daftar Mata Kuliah</h5>
    <a href="{{ route('matakuliah.create') }}" class="btn btn-sm text-white"
        style="background:#E91E8C;">
        <i class="bi bi-plus-circle"></i> Tambah Mata Kuliah
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead style="background:#FDE8F2;">
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Kode</th>
                    <th>SKS</th>
                    <th>Dosen</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mataKuliah as $i => $mk)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $mk->nama }}</td>
                    <td><span class="badge" style="background:#E91E8C;">{{ $mk->kode }}</span></td>
                    <td>{{ $mk->sks }} SKS</td>
                    <td>{{ $mk->dosen->user->name ?? '-' }}</td>
                    <td>
                        <a href="{{ route('matakuliah.edit', $mk->id) }}"
                            class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('matakuliah.destroy', $mk->id) }}"
                            method="POST" class="d-inline"
                            onsubmit="return confirm('Hapus mata kuliah ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        Belum ada data mata kuliah
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection