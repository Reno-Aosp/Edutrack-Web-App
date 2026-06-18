@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4" style="color:#5C1033;">Form Tambah User</h5>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control"
                        placeholder="Masukkan nama lengkap" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" name="email" class="form-control"
                        placeholder="email@example.com" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Password</label>
                    <input type="password" name="password" class="form-control"
                        placeholder="Min. 6 karakter" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Role</label>
                    <select name="role" id="roleSelect" class="form-select" required onchange="toggleMahasiswaFields()">
                        <option value="">-- Pilih Role --</option>
                        <option value="admin">Admin</option>
                        <option value="dosen">Dosen</option>
                        <option value="mahasiswa">Mahasiswa</option>
                    </select>
                </div>

                <!-- Field Mahasiswa (Hidden by default) -->
                <div id="mahasiswaFields" style="display: none;" class="col-12">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">NIM</label>
                            <input type="text" name="nim" class="form-control"
                                placeholder="Nomor Induk Mahasiswa">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Program Studi</label>
                            <input type="text" name="prodi" class="form-control"
                                placeholder="Contoh: Teknik Informatika">
                        </div>

                        <!-- Box untuk setiap Prodi -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold mb-3">Pilih Program Studi & Kelas</label>
                            <div class="row g-3" id="prodiBoxContainer">
                                @php
                                    $prodiList = $kelas->groupBy('prodi');
                                @endphp
                                @foreach($prodiList as $prodi => $kelasPerProdi)
                                <div class="col-md-6">
                                    <div class="card border-2 border-primary h-100">
                                        <div class="card-header bg-primary text-white fw-bold">
                                            {{ $prodi }}
                                        </div>
                                        <div class="card-body">
                                            <!-- Filter Angkatan -->
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Angkatan</label>
                                                <div class="d-flex flex-wrap gap-2 angkatan-filter" data-prodi="{{ $prodi }}">
                                                    @php
                                                        $angkatans = $kelasPerProdi->pluck('angkatan')->unique()->sort()->reverse();
                                                    @endphp
                                                    @foreach($angkatans as $ang)
                                                    <button type="button" class="btn btn-sm btn-outline-secondary angkatan-btn"
                                                        data-prodi="{{ $prodi }}" data-angkatan="{{ $ang }}"
                                                        onclick="filterByAngkatanSemester('{{ $prodi }}', '{{ $ang }}', null, this)">
                                                        {{ $ang }}
                                                    </button>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <!-- Filter Semester -->
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Semester</label>
                                                <div class="d-flex flex-wrap gap-2 semester-filter" data-prodi="{{ $prodi }}">
                                                    @php
                                                        $semesters = $kelasPerProdi->pluck('semester')->unique()->sort();
                                                    @endphp
                                                    @foreach($semesters as $sem)
                                                    <button type="button" class="btn btn-sm btn-outline-secondary semester-btn"
                                                        data-prodi="{{ $prodi }}" data-semester="{{ $sem }}"
                                                        onclick="filterByAngkatanSemester('{{ $prodi }}', null, '{{ $sem }}', this)">
                                                        Sem {{ $sem }}
                                                    </button>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <!-- List Kelas -->
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold">Kelas</label>
                                                <div class="kelas-list" data-prodi="{{ $prodi }}">
                                                    @foreach($kelasPerProdi as $k)
                                                    <div class="form-check mb-2 kelas-item"
                                                        data-prodi="{{ $prodi }}" data-angkatan="{{ $k->angkatan }}"
                                                        data-semester="{{ $k->semester }}">
                                                        <input class="form-check-input kelas-checkbox" type="checkbox"
                                                            name="kelas_ids[]" value="{{ $k->id }}" id="kelas_{{ $k->id }}">
                                                        <label class="form-check-label small" for="kelas_{{ $k->id }}">
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
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn text-white fw-bold"
                    style="background:#E91E8C;">
                    <i class="bi bi-save"></i> Simpan
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Batal
                </a>
            </div>
        </form>

        <script>
            function toggleMahasiswaFields() {
                const role = document.getElementById('roleSelect').value;
                const mahasiswaFields = document.getElementById('mahasiswaFields');
                
                if (role === 'mahasiswa') {
                    mahasiswaFields.style.display = 'block';
                } else {
                    mahasiswaFields.style.display = 'none';
                }
            }

            function filterByAngkatanSemester(prodi, angkatan, semester, button) {
                // Update button styles
                if (angkatan) {
                    // Angkatan button clicked
                    document.querySelectorAll(`.angkatan-filter[data-prodi="${prodi}"] .angkatan-btn`).forEach(btn => {
                        btn.classList.remove('btn-secondary');
                        btn.classList.add('btn-outline-secondary');
                    });
                    button.classList.remove('btn-outline-secondary');
                    button.classList.add('btn-secondary');
                } else if (semester) {
                    // Semester button clicked
                    document.querySelectorAll(`.semester-filter[data-prodi="${prodi}"] .semester-btn`).forEach(btn => {
                        btn.classList.remove('btn-secondary');
                        btn.classList.add('btn-outline-secondary');
                    });
                    button.classList.remove('btn-outline-secondary');
                    button.classList.add('btn-secondary');
                }

                // Filter kelas based on selection
                const kelasList = document.querySelector(`.kelas-list[data-prodi="${prodi}"]`);
                const kelasItems = kelasList.querySelectorAll('.kelas-item');

                kelasItems.forEach(item => {
                    let show = true;

                    // Get selected angkatan and semester for this prodi
                    const selectedAngkatan = document.querySelector(`.angkatan-filter[data-prodi="${prodi}"] .angkatan-btn.btn-secondary`);
                    const selectedSemester = document.querySelector(`.semester-filter[data-prodi="${prodi}"] .semester-btn.btn-secondary`);

                    if (selectedAngkatan && item.dataset.angkatan != selectedAngkatan.dataset.angkatan) {
                        show = false;
                    }
                    if (selectedSemester && item.dataset.semester != selectedSemester.dataset.semester) {
                        show = false;
                    }

                    item.style.display = show ? 'block' : 'none';
                });
            }
        </script>
    </div>
</div>
@endsection