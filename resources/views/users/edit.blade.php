@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4" style="color:#5C1033;">Form Edit User</h5>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('users.update', $user->id) }}">
            @csrf @method('PUT')
            <div class="row g-3">

                {{-- Nama --}}
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control"
                        value="{{ $user->name }}" required>
                </div>

                {{-- Email (read-only) --}}
                <div class="col-md-6">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" class="form-control"
                        value="{{ $user->email }}" disabled>
                </div>

                {{-- Role --}}
                <div class="col-md-6">
                    <label class="form-label fw-bold">Role</label>
                    <select name="role" id="roleSelect" class="form-select" required
                        onchange="toggleFieldsByRole()">
                        <option value="admin"     {{ $user->role == 'admin'     ? 'selected' : '' }}>Admin</option>
                        <option value="dosen"     {{ $user->role == 'dosen'     ? 'selected' : '' }}>Dosen</option>
                        <option value="mahasiswa" {{ $user->role == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                    </select>
                </div>

                {{-- Password Baru — hanya tampil untuk role admin --}}
                <div class="col-md-6" id="passwordField"
                    style="display: {{ $user->role === 'admin' ? 'block' : 'none' }};">
                    <label class="form-label fw-bold">
                        Password Baru
                        <span class="text-muted small">(kosongkan jika tidak diubah)</span>
                    </label>
                    <input type="password" name="password" class="form-control"
                        placeholder="Min. 6 karakter">
                </div>

                {{-- Field khusus Mahasiswa --}}
                <div id="mahasiswaFields" class="col-12"
                    style="display: {{ $user->role === 'mahasiswa' ? 'block' : 'none' }};">
                    <div class="row g-3">

                        {{-- NIM --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">NIM</label>
                            <input type="text" name="nim" class="form-control"
                                placeholder="Nomor Induk Mahasiswa"
                                value="{{ $user->mahasiswa->nim ?? '' }}">
                        </div>

                        {{-- Prodi (text) --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Program Studi</label>
                            <input type="text" name="prodi" class="form-control"
                                placeholder="Contoh: Teknik Informatika"
                                value="{{ $user->mahasiswa->prodi ?? '' }}">
                        </div>

                        {{-- Pilih Kelas — SINGLE SELECT (radio) --}}
                        <div class="col-12">
                            <label class="form-label fw-bold mb-3">Pilih Program Studi & Kelas</label>
                            <p class="text-muted small mb-3">
                                <i class="bi bi-info-circle"></i>
                                Pilih <strong>satu kelas</strong> untuk mahasiswa ini.
                            </p>
                            <div class="row g-3" id="prodiBoxContainer">
                                @php
                                    $prodiList      = $kelas->groupBy('prodi');
                                    $currentKelasId = optional($user->mahasiswa?->kelas->first())->id;
                                @endphp

                                @foreach($prodiList as $prodi => $kelasPerProdi)
                                <div class="col-md-6">
                                    <div class="card border-2 h-100"
                                        style="border-color:#E91E8C!important;">
                                        <div class="card-header text-white fw-bold"
                                            style="background:#E91E8C;">
                                            {{ $prodi }}
                                        </div>
                                        <div class="card-body">

                                            {{-- Filter Angkatan --}}
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Angkatan</label>
                                                <div class="d-flex flex-wrap gap-2 angkatan-filter"
                                                    data-prodi="{{ $prodi }}">
                                                    @php
                                                        $angkatans = $kelasPerProdi->pluck('angkatan')
                                                            ->unique()->sort()->reverse();
                                                    @endphp
                                                    @foreach($angkatans as $ang)
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-secondary angkatan-btn"
                                                        data-prodi="{{ $prodi }}"
                                                        data-angkatan="{{ $ang }}"
                                                        onclick="filterKelas('{{ $prodi }}', '{{ $ang }}', null, this)">
                                                        {{ $ang }}
                                                    </button>
                                                    @endforeach
                                                </div>
                                            </div>

                                            {{-- Filter Semester --}}
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Semester</label>
                                                <div class="d-flex flex-wrap gap-2 semester-filter"
                                                    data-prodi="{{ $prodi }}">
                                                    @php
                                                        $semesters = $kelasPerProdi->pluck('semester')
                                                            ->unique()->sort();
                                                    @endphp
                                                    @foreach($semesters as $sem)
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-secondary semester-btn"
                                                        data-prodi="{{ $prodi }}"
                                                        data-semester="{{ $sem }}"
                                                        onclick="filterKelas('{{ $prodi }}', null, '{{ $sem }}', this)">
                                                        Sem {{ $sem }}
                                                    </button>
                                                    @endforeach
                                                </div>
                                            </div>

                                            {{-- List Kelas — RADIO (single select) --}}
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold">Kelas</label>
                                                <div class="kelas-list" data-prodi="{{ $prodi }}">
                                                    @foreach($kelasPerProdi as $k)
                                                    <div class="form-check mb-2 kelas-item"
                                                        data-prodi="{{ $prodi }}"
                                                        data-angkatan="{{ $k->angkatan }}"
                                                        data-semester="{{ $k->semester }}">
                                                        {{-- FIX: radio bukan checkbox, name="kelas_id" (bukan array) --}}
                                                        <input class="form-check-input"
                                                            type="radio"
                                                            name="kelas_id"
                                                            value="{{ $k->id }}"
                                                            id="kelas_{{ $k->id }}"
                                                            {{ $currentKelasId == $k->id ? 'checked' : '' }}>
                                                        <label class="form-check-label small"
                                                            for="kelas_{{ $k->id }}">
                                                            {{ $k->nama_kelas }}
                                                        </label>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Tombol --}}
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn text-white fw-bold"
                    style="background:#E91E8C;">
                    <i class="bi bi-save"></i> Update
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
const userMahasiswa = @json($user->mahasiswa);

// ── Tampilkan/sembunyikan field sesuai role ──────────────────────────────────
function toggleFieldsByRole() {
    const role            = document.getElementById('roleSelect').value;
    const passwordField   = document.getElementById('passwordField');
    const mahasiswaFields = document.getElementById('mahasiswaFields');

    // Password hanya untuk admin
    passwordField.style.display   = (role === 'admin')     ? 'block' : 'none';
    // Field mahasiswa hanya untuk mahasiswa
    mahasiswaFields.style.display = (role === 'mahasiswa') ? 'block' : 'none';

    // Kosongkan password jika disembunyikan
    if (role !== 'admin') {
        const pwInput = document.querySelector('input[name="password"]');
        if (pwInput) pwInput.value = '';
    }
}

// ── Filter kelas berdasarkan angkatan / semester ─────────────────────────────
function filterKelas(prodi, angkatan, semester, clickedBtn) {
    // Toggle button active style
    if (angkatan !== null) {
        document.querySelectorAll(`.angkatan-filter[data-prodi="${prodi}"] .angkatan-btn`)
            .forEach(b => {
                b.classList.remove('btn-secondary');
                b.classList.add('btn-outline-secondary');
            });
    } else {
        document.querySelectorAll(`.semester-filter[data-prodi="${prodi}"] .semester-btn`)
            .forEach(b => {
                b.classList.remove('btn-secondary');
                b.classList.add('btn-outline-secondary');
            });
    }
    clickedBtn.classList.remove('btn-outline-secondary');
    clickedBtn.classList.add('btn-secondary');

    // Show/hide kelas items
    const items = document.querySelectorAll(`.kelas-list[data-prodi="${prodi}"] .kelas-item`);
    items.forEach(item => {
        const selAngkatan = document.querySelector(
            `.angkatan-filter[data-prodi="${prodi}"] .angkatan-btn.btn-secondary`
        );
        const selSemester = document.querySelector(
            `.semester-filter[data-prodi="${prodi}"] .semester-btn.btn-secondary`
        );

        let show = true;
        if (selAngkatan && item.dataset.angkatan != selAngkatan.dataset.angkatan) show = false;
        if (selSemester && item.dataset.semester != selSemester.dataset.semester) show = false;

        item.style.display = show ? 'block' : 'none';
    });
}

// ── Restore filter saat halaman load (untuk mahasiswa yang sudah punya kelas) ─
document.addEventListener('DOMContentLoaded', () => {
    if (!userMahasiswa || !userMahasiswa.prodi) return;
    if (document.getElementById('mahasiswaFields').style.display !== 'block') return;

    const prodi    = userMahasiswa.prodi;
    const angkatan = userMahasiswa.angkatan;

    // Klik tombol angkatan yang sesuai
    document.querySelectorAll(`.angkatan-filter[data-prodi="${prodi}"] .angkatan-btn`)
        .forEach(btn => {
            if (btn.dataset.angkatan == angkatan) btn.click();
        });

    // Klik tombol semester dari kelas pertama
    if (userMahasiswa.kelas && userMahasiswa.kelas.length > 0) {
        const sem = userMahasiswa.kelas[0].semester;
        document.querySelectorAll(`.semester-filter[data-prodi="${prodi}"] .semester-btn`)
            .forEach(btn => {
                if (btn.dataset.semester == sem) btn.click();
            });
    }
});
</script>
@endsection