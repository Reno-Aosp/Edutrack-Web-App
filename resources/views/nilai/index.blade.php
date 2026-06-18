@extends('layouts.app')

@section('title', 'Data Nilai')

@section('content')

{{-- Step 1: Pilih Tahun Ajaran --}}
@if(!isset($tahunAjaran))
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0" style="color:#5C1033;">Pilih Tahun Ajaran</h5>
        <button class="btn btn-sm text-white" style="background:#E91E8C;"
            data-bs-toggle="modal" data-bs-target="#modalTambahTahun">
            <i class="bi bi-plus-circle"></i> Tambah Tahun Ajaran
        </button>
    </div>
    <div class="row g-3">
        @foreach($semuaTahun as $tahun)
        <div class="col-md-4">
            <a href="{{ route('nilai.index', ['tahun' => $tahun]) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                    <div class="fw-bold fs-5" style="color:#5C1033;">
                        <i class="bi bi-calendar3 me-2"></i>{{ $tahun }}
                    </div>
                    <div class="text-muted small mt-1">Tahun Ajaran</div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
    <div class="modal fade" id="modalTambahTahun" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" style="color:#5C1033;">Tambah Tahun Ajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="GET" action="{{ route('nilai.index') }}">
                    <div class="modal-body">
                        <label class="form-label fw-bold">Tahun Mulai</label>
                        <input type="number" name="tahun_baru" class="form-control"
                            placeholder="Contoh: 2026" min="2020" max="2040"
                            value="{{ date('Y') }}" required>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn text-white fw-bold" style="background:#E91E8C;">Tambah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

{{-- Step 2: Pilih Semester --}}
@elseif(!isset($semester))
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0" style="color:#5C1033;">Pilih Semester</h5>
            <small class="text-muted">Tahun Ajaran {{ $tahunAjaran }}</small>
        </div>
        <a href="{{ route('nilai.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <a href="{{ route('nilai.index', ['tahun' => $tahunAjaran, 'semester' => 'Ganjil']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                    <div class="fw-bold fs-5" style="color:#5C1033;"><i class="bi bi-sun me-2"></i>Ganjil</div>
                    <div class="text-muted small mt-1">Semester Ganjil {{ $tahunAjaran }}</div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('nilai.index', ['tahun' => $tahunAjaran, 'semester' => 'Genap']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                    <div class="fw-bold fs-5" style="color:#5C1033;"><i class="bi bi-moon me-2"></i>Genap</div>
                    <div class="text-muted small mt-1">Semester Genap {{ $tahunAjaran }}</div>
                </div>
            </a>
        </div>
    </div>

{{-- Step 3: Pilih Kelas --}}
@elseif(!isset($kelas))
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0" style="color:#5C1033;">Pilih Kelas</h5>
            <small class="text-muted">{{ $semester }} {{ $tahunAjaran }}</small>
        </div>
        <a href="{{ route('nilai.index', ['tahun' => $tahunAjaran]) }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="row g-3">
        @forelse($semuaKelas as $k)
        <div class="col-md-4">
            <a href="{{ route('nilai.index', ['tahun' => $tahunAjaran, 'semester' => $semester, 'kelas_id' => $k->id]) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                    <div class="fw-bold fs-5" style="color:#5C1033;">{{ $k->nama_kelas }}</div>
                    <div class="text-muted small">{{ $k->prodi }}</div>
                    <div class="text-muted small">Angkatan {{ $k->angkatan }}</div>
                    <div class="mt-2">
                        <span class="badge" style="background:#E91E8C;">{{ $k->mahasiswa_count }} Mahasiswa</span>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12"><div class="alert alert-warning">Belum ada kelas.</div></div>
        @endforelse
    </div>

{{-- Step 4: Pilih Mata Kuliah --}}
@elseif(!isset($matkul))
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0" style="color:#5C1033;">Pilih Mata Kuliah</h5>
            <small class="text-muted">{{ $semester }} {{ $tahunAjaran }} · {{ $kelas->nama_kelas }}</small>
        </div>
        <a href="{{ route('nilai.index', ['tahun' => $tahunAjaran, 'semester' => $semester]) }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="row g-3">
        @forelse($semuaMatkul as $mk)
        <div class="col-md-4">
            <a href="{{ route('nilai.index', ['tahun' => $tahunAjaran, 'semester' => $semester, 'kelas_id' => $kelas->id, 'matkul_id' => $mk->id]) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                    <div class="fw-bold fs-5" style="color:#5C1033;">{{ $mk->nama }}</div>
                    <div class="text-muted small">Kode: {{ $mk->kode }}</div>
                    <div class="mt-2">
                        <span class="badge" style="background:#E91E8C;">{{ $mk->sks }} SKS</span>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12"><div class="alert alert-warning">Belum ada mata kuliah untuk kelas ini.</div></div>
        @endforelse
    </div>

{{-- Step 5: Pilih Mahasiswa + Tampil Nilai --}}
@else
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0" style="color:#5C1033;">
                Nilai · {{ $kelas->nama_kelas }} · {{ $matkul->nama }}
            </h5>
            <small class="text-muted">{{ $semester }} {{ $tahunAjaran }} · {{ $matkul->kode }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('nilai.index', ['tahun' => $tahunAjaran, 'semester' => $semester, 'kelas_id' => $kelas->id]) }}"
                class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    {{-- Filter Pilih Mahasiswa --}}
    @if(isset($semuaMahasiswaKelas))
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body p-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-bold small" style="color:#5C1033;">
                        Pilih Mahasiswa untuk Input/Lihat Nilai
                    </label>
                    <select id="pilihMahasiswa" class="form-select">
                        <option value="">-- Semua Mahasiswa --</option>
                        @foreach($semuaMahasiswaKelas as $mhs)
                        <option value="{{ $mhs->id }}">{{ $mhs->nama }} - {{ $mhs->nim }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button onclick="pilihMahasiswaAction('lihat')"
                        class="btn btn-outline-primary w-100">
                        <i class="bi bi-eye"></i> Lihat Nilai
                    </button>
                </div>
                <div class="col-md-3">
                    <button onclick="pilihMahasiswaAction('input')"
                        class="btn text-white fw-bold w-100" style="background:#E91E8C;">
                        <i class="bi bi-pencil"></i> Input Nilai
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function pilihMahasiswaAction(action) {
            const mahasiswaId = document.getElementById('pilihMahasiswa').value;
            const base = '{{ route('nilai.index') }}';
            const params = new URLSearchParams({
                tahun: '{{ $tahunAjaran }}',
                semester: '{{ $semester }}',
                kelas_id: '{{ $kelas->id }}',
                matkul_id: '{{ $matkul->id }}',
            });
            if (mahasiswaId) params.set('mahasiswa_id', mahasiswaId);

            if (action === 'input') {
                const createParams = new URLSearchParams({
                    kelas_id: '{{ $kelas->id }}',
                    matkul_id: '{{ $matkul->id }}',
                    semester: '{{ $semester }} {{ $tahunAjaran }}',
                });
                if (mahasiswaId) createParams.set('mahasiswa_id', mahasiswaId);
                window.location.href = '{{ route('nilai.create') }}?' + createParams.toString();
            } else {
                window.location.href = base + '?' + params.toString();
            }
        }
    </script>
    @else
    {{-- Tombol input nilai untuk mahasiswa yang sudah dipilih --}}
    <div class="mb-3 d-flex gap-2">
        <a href="{{ route('nilai.index', ['tahun' => $tahunAjaran, 'semester' => $semester, 'kelas_id' => $kelas->id, 'matkul_id' => $matkul->id]) }}"
            class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-people"></i> Semua Mahasiswa
        </a>
        <a href="{{ route('nilai.create', ['kelas_id' => $kelas->id, 'matkul_id' => $matkul->id, 'semester' => $semester . ' ' . $tahunAjaran, 'mahasiswa_id' => $mahasiswa->id ?? '']) }}"
            class="btn btn-sm text-white" style="background:#E91E8C;">
            <i class="bi bi-pencil"></i> Edit Nilai {{ $mahasiswa->nama ?? '' }}
        </a>
    </div>
    @endif

    {{-- Tabel Nilai --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            @if(isset($mahasiswa) && !isset($semuaMahasiswaKelas))
            <div class="mb-2 p-2 rounded" style="background:#FDE8F2;">
                <small class="fw-bold" style="color:#5C1033;">
                    <i class="bi bi-person"></i>
                    Menampilkan nilai: {{ $mahasiswa->nama }} ({{ $mahasiswa->nim }})
                </small>
            </div>
            @endif
            <table class="table table-hover align-middle">
                <thead style="background:#FDE8F2;">
                    <tr>
                        <th>No</th>
                        <th>Mahasiswa</th>
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
                        <td>{{ $n->nilai_tugas ?? '-' }}</td>
                        <td>{{ $n->nilai_uts ?? '-' }}</td>
                        <td>{{ $n->nilai_uas ?? '-' }}</td>
                        <td>
                            @php $na = $n->nilai_akhir; @endphp
                            <span class="badge {{ $na >= 60 ? 'bg-success' : 'bg-danger' }}">
                                {{ round($na, 1) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('nilai.edit', $n->id) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('nilai.destroy', $n->id) }}" method="POST"
                                class="d-inline" onsubmit="return confirm('Hapus nilai ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-3">
                            Belum ada data nilai
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection