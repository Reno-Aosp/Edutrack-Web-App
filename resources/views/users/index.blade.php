@extends('layouts.app')

@section('title', 'Daftar User')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold" style="color:#5C1033;">Daftar User</h5>
    <a href="{{ route('users.create') }}" class="btn btn-sm text-white"
        style="background:#E91E8C;">
        <i class="bi bi-plus-circle"></i> Tambah User
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead style="background:#FDE8F2;">
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $i => $user)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if($user->role == 'admin')
                            <span class="badge" style="background:#5C1033;">Admin</span>
                        @elseif($user->role == 'dosen')
                            <span class="badge" style="background:#E91E8C;">Dosen</span>
                        @else
                            <span class="badge bg-secondary">Mahasiswa</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('users.edit', $user->id) }}"
                            class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('users.destroy', $user->id) }}"
                            method="POST" class="d-inline"
                            onsubmit="return confirm('Hapus user ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">Belum ada user</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection