@extends('layouts.app')

@section('title', 'Kelola Sesi Absensi')

@section('content')

{{-- ================= ALERT SUCCESS ================= --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}

        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert">
        </button>
    </div>
@endif

{{-- ================= ALERT ERROR ================= --}}
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}

        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert">
        </button>
    </div>
@endif

{{-- ================= VALIDATION ERROR ================= --}}
@if($errors->any())

    <div class="alert alert-danger alert-dismissible fade show">

        <div class="fw-bold mb-2">
            <i class="bi bi-exclamation-triangle"></i>
            Terjadi kesalahan:
        </div>

        @foreach($errors->all() as $error)

            <div>
                • {{ $error }}
            </div>

        @endforeach

        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert">
        </button>

    </div>

@endif

{{-- ================= FORM BUKA SESI ================= --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">

    <div class="card-body p-4">

        <h5 class="fw-bold mb-3"
            style="color:#5C1033;">

            Buka Sesi Absensi
        </h5>

        <form method="POST"
              action="{{ route('sesi-absensi.store') }}">

            @csrf

            <div class="row g-3 align-items-end">

                {{-- ================= KELAS ================= --}}
                <div class="col-md-3">

                    <label class="form-label fw-bold">
                        Kelas
                    </label>

                    <select name="kelas_id"
                            id="selectKelas"
                            class="form-select @error('kelas_id') is-invalid @enderror"
                            required>

                        <option value="">
                            -- Pilih Kelas --
                        </option>

                        @foreach($kelas as $k)

                            <option value="{{ $k->id }}"
                                {{ old('kelas_id') == $k->id ? 'selected' : '' }}>

                                {{ $k->nama_kelas }}
                            </option>

                        @endforeach

                    </select>

                    @error('kelas_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                {{-- ================= MATA KULIAH ================= --}}
                <div class="col-md-3">

                    <label class="form-label fw-bold">
                        Mata Kuliah
                    </label>

                    <select name="matkul_id"
                            id="selectMatkul"
                            class="form-select @error('matkul_id') is-invalid @enderror"
                            required
                            disabled>

                        <option value="">
                            -- Pilih Kelas Dulu --
                        </option>

                    </select>

                    @error('matkul_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                {{-- ================= JAM BUKA ================= --}}
                <div class="col-md-2">

                    <label class="form-label fw-bold">
                        Jam Buka
                    </label>

                    <input type="time"
                           name="jam_buka"
                           class="form-control @error('jam_buka') is-invalid @enderror"
                           value="{{ old('jam_buka', date('H:i')) }}"
                           required>

                    @error('jam_buka')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                {{-- ================= JAM TUTUP ================= --}}
                <div class="col-md-2">

                    <label class="form-label fw-bold">

                        Jam Tutup

                        <span class="text-danger">*</span>
                    </label>

                    <input type="time"
                           name="jam_tutup"
                           class="form-control @error('jam_tutup') is-invalid @enderror"
                           value="{{ old('jam_tutup') }}"
                           required>

                    @error('jam_tutup')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                    <small class="text-muted">
                        Wajib diisi
                    </small>

                </div>

                {{-- ================= PERTEMUAN ================= --}}
                <div class="col-md-1">

                    <label class="form-label fw-bold">
                        Pertemuan
                    </label>

                    <input type="number"
                           name="pertemuan_ke"
                           class="form-control @error('pertemuan_ke') is-invalid @enderror"
                           value="{{ old('pertemuan_ke') }}"
                           placeholder="1"
                           min="1">

                    @error('pertemuan_ke')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                {{-- ================= BUTTON ================= --}}
                <div class="col-md-1">

                    <label class="form-label fw-bold">
                        &nbsp;
                    </label>

                    <button type="submit"
                            class="btn text-white fw-bold w-100 d-block"
                            style="background:#E91E8C;">

                        <i class="bi bi-unlock"></i>

                        Buka
                    </button>

                </div>

            </div>

        </form>

    </div>
</div>

{{-- ================= TABEL SESI ================= --}}
<div class="card border-0 shadow-sm rounded-4">

    <div class="card-body p-4">

        <h5 class="fw-bold mb-3"
            style="color:#5C1033;">

            Riwayat Sesi Absensi
        </h5>

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead style="background:#FFF0F7;">

                    <tr>

                        <th>Tanggal</th>

                        <th>Mata Kuliah</th>

                        <th>Kelas</th>

                        <th>Pertemuan</th>

                        <th>Jam Buka</th>

                        <th>Jam Tutup</th>

                        <th>Status</th>

                        <th>Aksi</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($sesi as $s)

                        <tr>

                            <td>
                                {{ \Carbon\Carbon::parse($s->tanggal)->format('d M Y') }}
                            </td>

                            <td class="fw-bold">
                                {{ $s->mataKuliah->nama ?? '-' }}
                            </td>

                            <td>
                                {{ $s->kelas->nama_kelas ?? '-' }}
                            </td>

                            <td>
                                {{ $s->pertemuan_ke ? 'Ke-'.$s->pertemuan_ke : '-' }}
                            </td>

                            <td>
                                {{ $s->jam_buka ?? '-' }}
                            </td>

                            <td>
                                {{ $s->jam_tutup ?? '-' }}
                            </td>

                            {{-- ================= STATUS ================= --}}
                            <td>

                                @if($s->status == 'buka')

                                    <span class="badge rounded-pill"
                                          style="background:#E91E8C;">

                                        <i class="bi bi-unlock"></i>

                                        Dibuka
                                    </span>

                                @else

                                    <span class="badge bg-secondary rounded-pill">

                                        <i class="bi bi-lock"></i>

                                        Ditutup
                                    </span>

                                @endif

                            </td>

                            {{-- ================= AKSI ================= --}}
                            <td class="d-flex gap-1">

                                @if($s->status == 'buka')

                                    <form method="POST"
                                          action="{{ route('sesi-absensi.tutup', $s->id) }}">

                                        @csrf
                                        @method('PATCH')

                                        <button class="btn btn-sm btn-warning fw-bold">

                                            <i class="bi bi-lock"></i>

                                            Tutup
                                        </button>

                                    </form>

                                @endif

                                <form method="POST"
                                      action="{{ route('sesi-absensi.destroy', $s->id) }}"
                                      onsubmit="return confirm('Hapus sesi ini?')">

                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-sm btn-danger">

                                        <i class="bi bi-trash"></i>

                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8"
                                class="text-center text-muted py-4">

                                Belum ada sesi absensi
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- ================= PAGINATION ================= --}}
        {{ $sesi->links() }}

    </div>

</div>

{{-- ================= AJAX FILTER MATKUL ================= --}}
<script>

document.getElementById('selectKelas').addEventListener('change', function () {

    const kelasId = this.value;

    const matkulSelect = document.getElementById('selectMatkul');

    // jika belum pilih kelas
    if (!kelasId) {

        matkulSelect.innerHTML =
            '<option value="">-- Pilih Kelas Dulu --</option>';

        matkulSelect.disabled = true;

        return;
    }

    // loading
    matkulSelect.innerHTML =
        '<option value="">Memuat...</option>';

    matkulSelect.disabled = true;

    fetch(`/api-internal/kelas/${kelasId}/matkul`)

        .then(response => response.json())

        .then(data => {

            matkulSelect.disabled = false;

            // kosong
            if (data.length === 0) {

                matkulSelect.innerHTML =
                    '<option value="">Tidak ada mata kuliah di kelas ini</option>';

            } else {

                let options =
                    '<option value="">-- Pilih Mata Kuliah --</option>';

                data.forEach(mk => {

                    options += `
                        <option value="${mk.id}">
                            ${mk.nama} (${mk.kode})
                        </option>
                    `;

                });

                matkulSelect.innerHTML = options;
            }

        })

        .catch(error => {

            console.log(error);

            matkulSelect.disabled = false;

            matkulSelect.innerHTML =
                '<option value="">Gagal memuat data</option>';

        });

});

</script>

@endsection