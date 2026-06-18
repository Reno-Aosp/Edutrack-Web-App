<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Nilai;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NilaiWebController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // Helper: ambil kelas & matkul sesuai role
    // ─────────────────────────────────────────────────────────────
    private function getDosenMatkulIds($user)
    {
        if ($user->role !== 'dosen') {
            return null;
        }

        $dosen = $user->dosen;

        return $dosen
            ? MataKuliah::where('dosen_id', $dosen->id)->pluck('id')
            : collect();
    }

    private function getKelasForUser($user, $matkulIds = null)
    {
        if ($user->role === 'dosen') {


            return Kelas::withCount('mahasiswa')
                ->whereHas('mataKuliah', fn($q) =>
                    $q->whereIn('mata_kuliah.id', $matkulIds))
                ->get();
        }

        return Kelas::withCount('mahasiswa')->get();
    }

    public function index(Request $request)
    {
        $user      = Auth::user();
        $matkulIds = $this->getDosenMatkulIds($user);
        $isDosen   = $user->role === 'dosen';

        // ── Bangun daftar tahun ──
        $tahunDariDB = Nilai::distinct()
            ->pluck('semester')
            ->map(fn($s) => preg_replace('/^(Ganjil|Genap)\s+/', '', $s))
            ->filter()
            ->unique()
            ->toArray();

        $tahunDariKelas = Kelas::distinct()
            ->pluck('angkatan')
            ->map(fn($a) => $a . '/' . ($a + 1))
            ->toArray();

        $tahunSekarang = (int) date('Y');
        $tahunOtomatis = [];

        for ($t = $tahunSekarang - 2; $t <= $tahunSekarang + 1; $t++) {
            $tahunOtomatis[] = $t . '/' . ($t + 1);
        }

        $semuaTahun = array_unique(array_merge(
            $tahunDariDB,
            $tahunDariKelas,
            $tahunOtomatis
        ));

        sort($semuaTahun);

        if ($request->tahun_baru) {

            $t = (int) $request->tahun_baru;

            $semuaTahun[] = $t . '/' . ($t + 1);
            $semuaTahun   = array_unique($semuaTahun);

            sort($semuaTahun);

            return redirect()->route('nilai.index', [
                'tahun' => $t . '/' . ($t + 1)
            ]);
        }

        $tahunAjaran   = $request->tahun;
        $semesterPilih = $request->semester;

        // ─────────────────────────────────────────────────────────
        // STEP 5: Nilai per mahasiswa
        // ─────────────────────────────────────────────────────────
        if (
            $tahunAjaran &&
            $semesterPilih &&
            $request->kelas_id &&
            $request->matkul_id &&
            $request->mahasiswa_id
        ) {

            $kelas     = Kelas::findOrFail($request->kelas_id);
            $matkul    = MataKuliah::findOrFail($request->matkul_id);
            $mahasiswa = Mahasiswa::findOrFail($request->mahasiswa_id);

            $semester     = $semesterPilih;
            $semesterFull = $semesterPilih . ' ' . $tahunAjaran;

            $nilai = Nilai::with(['mahasiswa', 'mataKuliah'])
                ->where('kelas_id', $request->kelas_id)
                ->where('matkul_id', $request->matkul_id)
                ->where('mahasiswa_id', $request->mahasiswa_id)
                ->where('semester', $semesterFull)
                ->get();

            return view('nilai.index', compact(
                'kelas',
                'matkul',
                'mahasiswa',
                'nilai',
                'semester',
                'tahunAjaran',
                'semuaTahun'
            ));
        }

        // ─────────────────────────────────────────────────────────
        // STEP 4: Nilai semua mahasiswa
        // ─────────────────────────────────────────────────────────
        if (
            $tahunAjaran &&
            $semesterPilih &&
            $request->kelas_id &&
            $request->matkul_id
        ) {

            $kelas        = Kelas::findOrFail($request->kelas_id);
            $matkul       = MataKuliah::findOrFail($request->matkul_id);

            $semester     = $semesterPilih;
            $semesterFull = $semesterPilih . ' ' . $tahunAjaran;

            $nilai = Nilai::with(['mahasiswa', 'mataKuliah'])
                ->where('kelas_id', $request->kelas_id)
                ->where('matkul_id', $request->matkul_id)
                ->where('semester', $semesterFull)
                ->get();

            $semuaMahasiswaKelas = $kelas->mahasiswa;

            return view('nilai.index', compact(
                'kelas',
                'matkul',
                'nilai',
                'semester',
                'tahunAjaran',
                'semuaTahun',
                'semuaMahasiswaKelas'
            ));
        }

        // ─────────────────────────────────────────────────────────
        // STEP 3: Pilih matkul
        // ─────────────────────────────────────────────────────────
        if ($tahunAjaran && $semesterPilih && $request->kelas_id) {

            $kelas    = Kelas::findOrFail($request->kelas_id);
            $semester = $semesterPilih;

            $semuaMatkul = $isDosen
                ? $kelas->mataKuliah
                    ->whereIn('id', $matkulIds->toArray())
                    ->values()
                : $kelas->mataKuliah;

            return view('nilai.index', compact(
                'kelas',
                'semester',
                'tahunAjaran',
                'semuaMatkul',
                'semuaTahun'
            ));
        }

        // ─────────────────────────────────────────────────────────
        // STEP 2: Pilih kelas
        // ─────────────────────────────────────────────────────────
        if ($tahunAjaran && $semesterPilih) {

            $semester = $semesterPilih;

            if ($isDosen) {

                $semuaKelas = Kelas::withCount('mahasiswa')
                    ->whereHas('mataKuliah', fn($q) =>
                        $q->whereIn('mata_kuliah.id', $matkulIds))
                    ->get();

            } else {

                $tahunMulai = (int) explode('/', $tahunAjaran)[0];

                $semuaKelas = Kelas::withCount('mahasiswa')
                    ->where('angkatan', $tahunMulai)
                    ->get();
            }

            return view('nilai.index', compact(
                'semester',
                'tahunAjaran',
                'semuaKelas',
                'semuaTahun'
            ));
        }

        // ─────────────────────────────────────────────────────────
        // STEP 1: Pilih tahun
        // ─────────────────────────────────────────────────────────
        if ($tahunAjaran) {

            return view('nilai.index', compact(
                'tahunAjaran',
                'semuaTahun'
            ));
        }

        return view('nilai.index', compact('semuaTahun'));
    }

    public function create(Request $request)
    {
        $kelas_id     = $request->kelas_id;
        $matkul_id    = $request->matkul_id;
        $semester     = $request->semester;
        $mahasiswa_id = $request->mahasiswa_id;

        $kelas  = Kelas::findOrFail($kelas_id);
        $matkul = MataKuliah::findOrFail($matkul_id);

        $mahasiswa = $mahasiswa_id
            ? $kelas->mahasiswa->where('id', $mahasiswa_id)
            : $kelas->mahasiswa;

        return view('nilai.create', compact(
            'kelas',
            'matkul',
            'mahasiswa',
            'semester'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kelas_id'  => 'required|exists:kelas,id',
            'matkul_id' => 'required|exists:mata_kuliah,id',
            'semester'  => 'required|string',

            'tugas'     => 'required|array',
            'tugas.*'   => 'nullable|numeric|min:0|max:100',

            'uts'       => 'required|array',
            'uts.*'     => 'nullable|numeric|min:0|max:100',

            'uas'       => 'required|array',
            'uas.*'     => 'nullable|numeric|min:0|max:100',
        ]);

        // Validasi dosen hanya bisa input nilai matkulnya
        $user = Auth::user();

        if ($user->role === 'dosen') {

            $dosen  = $user->dosen;
            $matkul = MataKuliah::find($request->matkul_id);

            if (!$matkul || !$dosen || $matkul->dosen_id != $dosen->id) {

                return back()->with(
                    'error',
                    'Anda tidak berhak input nilai untuk mata kuliah ini!'
                );
            }
        }

        $semesterFull = $request->semester;

        $mahasiswaIds = array_unique(array_merge(
            array_keys($request->tugas ?? []),
            array_keys($request->uts ?? []),
            array_keys($request->uas ?? [])
        ));

        foreach ($mahasiswaIds as $mahasiswa_id) {

            $tugas = $request->tugas[$mahasiswa_id] ?? null;
            $uts   = $request->uts[$mahasiswa_id] ?? null;
            $uas   = $request->uas[$mahasiswa_id] ?? null;

            if ($tugas || $uts || $uas) {

                $nilaiAkhir = ($tugas && $uts && $uas)
                    ? ($tugas + $uts + $uas) / 3
                    : null;

                Nilai::updateOrCreate(
                    [
                        'kelas_id'     => $request->kelas_id,
                        'matkul_id'    => $request->matkul_id,
                        'mahasiswa_id' => $mahasiswa_id,
                        'semester'     => $semesterFull,
                    ],
                    [
                        'nilai_tugas' => $tugas,
                        'nilai_uts'   => $uts,
                        'nilai_uas'   => $uas,
                        'nilai_akhir' => $nilaiAkhir,
                    ]
                );
            }
        }

        return redirect()
            ->route('nilai.index')
            ->with('success', 'Nilai berhasil disimpan');
    }

    public function edit($id)
    {
        $nilai      = Nilai::findOrFail($id);
        $mahasiswa  = Mahasiswa::all();
        $mataKuliah = MataKuliah::all();

        return view('nilai.edit', compact(
            'nilai',
            'mahasiswa',
            'mataKuliah'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswa,id',
            'matkul_id'    => 'required|exists:mata_kuliah,id',
            'nilai_tugas'  => 'nullable|numeric|min:0|max:100',
            'nilai_uts'    => 'nullable|numeric|min:0|max:100',
            'nilai_uas'    => 'nullable|numeric|min:0|max:100',
            'semester'     => 'required|string',
        ]);

        $nilai = Nilai::findOrFail($id);

        $nilaiAkhir = (
            $request->nilai_tugas &&
            $request->nilai_uts &&
            $request->nilai_uas
        )
            ? (
                $request->nilai_tugas +
                $request->nilai_uts +
                $request->nilai_uas
            ) / 3
            : null;

        $nilai->update([
            'mahasiswa_id' => $request->mahasiswa_id,
            'matkul_id'    => $request->matkul_id,
            'nilai_tugas'  => $request->nilai_tugas,
            'nilai_uts'    => $request->nilai_uts,
            'nilai_uas'    => $request->nilai_uas,
            'nilai_akhir'  => $nilaiAkhir,
            'semester'     => $request->semester,
        ]);

        return redirect()
            ->route('nilai.index')
            ->with('success', 'Nilai berhasil diperbarui');
    }

    public function destroy($id)
    {
        Nilai::findOrFail($id)->delete();

        return back()->with(
            'success',
            'Nilai berhasil dihapus'
        );
    }
}