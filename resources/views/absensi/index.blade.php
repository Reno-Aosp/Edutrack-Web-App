@extends('layouts.app')

@section('title', 'Data Absensi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold" style="color:#5C1033;">Daftar Absensi</h5>
    <a href="{{ route('absensi.create') }}" class="btn btn-sm text-white"
        style="background:#E91E8C;">
        <i class="bi bi-plus-circle"></i> Input Absensi
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead style="background:#FDE8F2;">
                <tr>
                    <th>No</th>
                    <th>Mahasiswa</th>
                    <th>Mata Kuliah</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($absensi as $i => $a)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $a->mahasiswa->user->name }}</td>
                    <td>{{ $a->mataKuliah->nama }}</td>
                    <td>{{ $a->tanggal }}</td>
                    <td>
                        @if($a->status == 'hadir')
                            <span class="badge bg-success">Hadir</span>
                        @elseif($a->status == 'sakit')
                            <span class="badge bg-warning">Sakit</span>
                        @elseif($a->status == 'izin')
                            <span class="badge bg-info">Izin</span>
                        @else
                            <span class="badge bg-danger">Alpha</span>
                        @endif
                    </td>
                    <td>{{ $a->keterangan ?? '-' }}</td>
                    <td>
                        <a href="{{ route('absensi.edit', $a->id) }}"
                            class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('absensi.destroy', $a->id) }}"
                            method="POST" class="d-inline"
                            onsubmit="return confirm('Hapus absensi ini?')">
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
                        Belum ada data absensi
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection