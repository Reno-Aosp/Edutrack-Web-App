@extends('layouts.app')

@section('title', 'Data Kelas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold" style="color:#5C1033;">Pilih Kelas</h5>
    <a href="{{ route('kelas.create') }}" class="btn btn-sm text-white"
        style="background:#E91E8C;">
        <i class="bi bi-plus-circle"></i> Tambah Kelas
    </a>
</div>

@if($kelas->isEmpty())
<div class="alert alert-info">
    Belum ada data kelas. <a href="{{ route('kelas.create') }}">Tambah kelas sekarang</a>
</div>
@else
<div class="row g-3" id="kelasGrid">
    @foreach($kelas as $k)
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100 kelas-card" style="cursor: pointer; transition: all 0.3s;" 
            onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 20px rgba(0,0,0,0.1)';"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)'"
            onclick="expandKelas({{ $k->id }}, '{{ $k->nama_kelas }}', '{{ $k->prodi }}', {{ $k->angkatan }}, {{ $k->semester }})">
            <div class="card-body">
                <h6 class="card-title fw-bold mb-2" style="color:#5C1033;">{{ $k->nama_kelas }}</h6>
                <p class="text-muted small mb-1">{{ $k->prodi }}</p>
                <p class="text-muted small mb-3">Angkatan {{ $k->angkatan }} - Semester {{ $k->semester }}</p>
                
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge" style="background:#E91E8C;">
                        {{ $k->mahasiswa_count }} Mahasiswa
                    </span>
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('kelas.show', $k->id) }}" class="btn btn-info text-white" 
                            title="Lihat detail" onclick="event.stopPropagation();">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('kelas.edit', $k->id) }}" class="btn btn-warning"
                            title="Edit" onclick="event.stopPropagation();">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('kelas.destroy', $k->id) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('Hapus kelas ini?'); event.stopPropagation();"
                            onclick="event.stopPropagation();">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger" type="submit" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

<!-- Modal untuk expand kelas -->
<div class="modal fade" id="kelasModal" tabindex="-1" aria-labelledby="kelasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0">
            <div class="modal-header" style="background:#5C1033; color:white;">
                <h5 class="modal-title fw-bold" id="kelasModalLabel">Detail Kelas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <label class="form-label fw-bold">Nama Kelas</label>
                    <p id="modalNamaKelas" class="form-control-plaintext fw-bold" style="color:#5C1033; font-size:1.2rem;"></p>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Program Studi</label>
                        <p id="modalProdi" class="form-control-plaintext"></p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Angkatan</label>
                        <p id="modalAngkatan" class="form-control-plaintext"></p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Semester</label>
                        <p id="modalSemester" class="form-control-plaintext"></p>
                    </div>
                </div>

                <hr>

                <!-- Filter Step 1: Pilih tipe filter -->
                <div id="stepOne" class="mb-4">
                    <h6 class="fw-bold mb-3">Pilih Filter untuk Melihat Data</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="selectFilterType('role')">
                            <i class="bi bi-person"></i> Berdasarkan Role
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="selectFilterType('semester')">
                            <i class="bi bi-calendar"></i> Berdasarkan Semester
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="selectFilterType('angkatan')">
                            <i class="bi bi-mortarboard"></i> Berdasarkan Angkatan
                        </button>
                    </div>
                </div>

                <!-- Filter Step 2: Pilih nilai filter -->
                <div id="stepTwo" class="mb-4" style="display: none;">
                    <h6 class="fw-bold mb-3">Pilih <span id="filterTypeLabel"></span></h6>
                    <div class="d-flex flex-wrap gap-2" id="filterOptions">
                        <!-- Options akan di-generate oleh JS -->
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm mt-3" onclick="resetFilter()">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </button>
                </div>

                <!-- Step 3: Tampilkan data -->
                <div id="stepThree" class="mb-4" style="display: none;">
                    <h6 class="fw-bold mb-3">Data <span id="selectedFilterLabel"></span></h6>
                    <div id="dataList" class="list-group">
                        <!-- Data akan di-generate oleh JS -->
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm mt-3" onclick="backToFilterSelect()">
                        <i class="bi bi-arrow-left"></i> Ubah Filter
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Prepare kelas data in PHP and pass to JS
    const kelasDataMap = {
        @foreach($kelas as $k)
        {{ $k->id }}: {
            id: {{ $k->id }},
            nama_kelas: '{{ $k->nama_kelas }}',
            prodi: '{{ $k->prodi }}',
            angkatan: {{ $k->angkatan }},
            semester: {{ $k->semester }},
            mahasiswa: [
                @foreach($k->mahasiswa as $m)
                {
                    id: {{ $m->id }},
                    nama: '{{ $m->nama }}',
                    nim: '{{ $m->nim }}',
                    user: {{ $m->user ? "{name: '" . $m->user->name . "', email: '" . $m->user->email . "', role: '" . $m->user->role . "'}" : 'null' }}
                },
                @endforeach
            ]
        },
        @endforeach
    };

    let currentKelasId = null;
    let currentFilterType = null;
    let currentFilterValue = null;

    function expandKelas(kelasId, namaKelas, prodi, angkatan, semester) {
        currentKelasId = kelasId;
        
        document.getElementById('modalNamaKelas').textContent = namaKelas;
        document.getElementById('modalProdi').textContent = prodi;
        document.getElementById('modalAngkatan').textContent = angkatan;
        document.getElementById('modalSemester').textContent = 'Semester ' + semester;

        // Reset steps
        document.getElementById('stepOne').style.display = 'block';
        document.getElementById('stepTwo').style.display = 'none';
        document.getElementById('stepThree').style.display = 'none';
        currentFilterType = null;
        currentFilterValue = null;

        const modal = new bootstrap.Modal(document.getElementById('kelasModal'));
        modal.show();
    }

    function selectFilterType(type) {
        currentFilterType = type;
        document.getElementById('stepOne').style.display = 'none';
        document.getElementById('stepTwo').style.display = 'block';
        document.getElementById('stepThree').style.display = 'none';

        let typeLabel = '';
        let options = [];

        if (type === 'role') {
            typeLabel = 'Role';
            const roles = new Set();
            const khalasData = kelasDataMap[currentKelasId];
            khalasData.mahasiswa.forEach(m => {
                if (m.user && m.user.role) {
                    roles.add(m.user.role);
                }
            });
            options = Array.from(roles).sort();
        } 
        else if (type === 'semester') {
            typeLabel = 'Semester';
            const semesters = new Set();
            const khalasData = kelasDataMap[currentKelasId];
            khalasData.mahasiswa.forEach(m => {
                semesters.add(khalasData.semester);
            });
            options = Array.from(semesters).sort((a, b) => a - b);
        } 
        else if (type === 'angkatan') {
            typeLabel = 'Angkatan';
            const angkatans = new Set();
            const khalasData = kelasDataMap[currentKelasId];
            khalasData.mahasiswa.forEach(m => {
                angkatans.add(khalasData.angkatan);
            });
            options = Array.from(angkatans).sort((a, b) => b - a);
        }

        document.getElementById('filterTypeLabel').textContent = typeLabel;

        let html = '';
        options.forEach(option => {
            const displayText = type === 'role' ? 
                (option.charAt(0).toUpperCase() + option.slice(1)) : 
                (type === 'semester' ? 'Semester ' + option : option);
            
            html += `<button type="button" class="btn btn-outline-primary" 
                onclick="selectFilterValue('${option}')">
                ${displayText}
            </button>`;
        });

        document.getElementById('filterOptions').innerHTML = html;
    }

    function selectFilterValue(value) {
        currentFilterValue = value;
        document.getElementById('stepTwo').style.display = 'none';
        document.getElementById('stepThree').style.display = 'block';

        const khalasData = kelasDataMap[currentKelasId];
        let filteredData = khalasData.mahasiswa;
        let label = '';

        if (currentFilterType === 'role') {
            filteredData = filteredData.filter(m => m.user && m.user.role === value);
            label = value.charAt(0).toUpperCase() + value.slice(1);
        } 
        else if (currentFilterType === 'semester') {
            filteredData = filteredData.filter(m => khalasData.semester == value);
            label = 'Semester ' + value;
        } 
        else if (currentFilterType === 'angkatan') {
            filteredData = filteredData.filter(m => khalasData.angkatan == value);
            label = 'Angkatan ' + value;
        }

        document.getElementById('selectedFilterLabel').textContent = label;

        let html = '';
        if (filteredData.length === 0) {
            html = '<div class="alert alert-info">Tidak ada data untuk filter ini</div>';
        } else {
            filteredData.forEach(m => {
                html += `
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 fw-bold">${m.nama}</h6>
                                <p class="mb-0 text-muted small">NIM: ${m.nim}</p>
                                ${m.user ? `<p class="mb-0 text-muted small">Email: ${m.user.email}</p>` : ''}
                            </div>
                            ${m.user ? `<span class="badge" style="background:#E91E8C;">${m.user.role}</span>` : ''}
                        </div>
                    </div>
                `;
            });
        }

        document.getElementById('dataList').innerHTML = html;
    }

    function resetFilter() {
        document.getElementById('stepOne').style.display = 'block';
        document.getElementById('stepTwo').style.display = 'none';
        document.getElementById('stepThree').style.display = 'none';
        currentFilterType = null;
        currentFilterValue = null;
    }

    function backToFilterSelect() {
        document.getElementById('stepOne').style.display = 'none';
        document.getElementById('stepTwo').style.display = 'block';
        document.getElementById('stepThree').style.display = 'none';
        currentFilterValue = null;
    }
</script>
@endsection