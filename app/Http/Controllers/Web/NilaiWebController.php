<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Nilai;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;

class NilaiWebController extends Controller {

    public function index(Request $request) {
        // Generate tahun otomatis
        $tahunSekarang = (int) date('Y');
        $semuaTahun = [];
        for ($t = $tahunSekarang - 2; $t <= $tahunSekarang + 1; $t++) {
            $semuaTahun[] = $t . '/' . ($t + 1);
        }

    // Tambah tahun dari DB
    $tahunDariDB = Nilai::distinct()->pluck('semester')
        ->map(fn($s) => preg_replace('/^(Ganjil|Genap)\s+/', '', $s))
        ->unique()->toArray();
    $semuaTahun = array_unique(array_merge($semuaTahun, $tahunDariDB));
    sort($semuaTahun);

    // Tambah tahun baru via modal
    if ($request->tahun_baru) {
        $t = (int) $request->tahun_baru;
        $semuaTahun[] = $t . '/' . ($t + 1);
        $semuaTahun = array_unique($semuaTahun);
        sort($semuaTahun);
        return redirect()->route('nilai.index', ['tahun' => $t . '/' . ($t + 1)]);
    }

    $tahunAjaran = $request->tahun;
    $semesterPilih = $request->semester; // 'Ganjil' atau 'Genap'

    // Step 5: Tampil nilai
    if ($tahunAjaran && $semesterPilih && $request->kelas_id && $request->matkul_id) {
        $kelas       = Kelas::findOrFail($request->kelas_id);
        $matkul      = MataKuliah::findOrFail($request->matkul_id);
        $semester    = $semesterPilih;
        $semesterFull = $semesterPilih . ' ' . $tahunAjaran;
        $nilai       = Nilai::with(['mahasiswa', 'mataKuliah'])
                        ->where('kelas_id', $request->kelas_id)
                        ->where('matkul_id', $request->matkul_id)
                        ->where('semester', $semesterFull)
                        ->get();
        return view('nilai.index', compact('kelas', 'matkul', 'nilai', 'semester', 'tahunAjaran', 'semuaTahun'));
    }

    // Step 4: Pilih matkul
    if ($tahunAjaran && $semesterPilih && $request->kelas_id) {
        $kelas       = Kelas::findOrFail($request->kelas_id);
        $semester    = $semesterPilih;
        $semuaMatkul = $kelas->mataKuliah;
        return view('nilai.index', compact('kelas', 'semester', 'tahunAjaran', 'semuaMatkul', 'semuaTahun'));
    }

    // Step 3: Pilih kelas
    if ($tahunAjaran && $semesterPilih) {
        $semester   = $semesterPilih;
        $semuaKelas = Kelas::withCount('mahasiswa')->get();
        return view('nilai.index', compact('semester', 'tahunAjaran', 'semuaKelas', 'semuaTahun'));
    }

    // Step 2: Pilih semester
    if ($tahunAjaran) {
        return view('nilai.index', compact('tahunAjaran', 'semuaTahun'));
    }

    // Step 1: Pilih tahun
    return view('nilai.index', compact('semuaTahun'));
    }

    public function create(Request $request) {
        $kelas_id = $request->kelas_id;
        $matkul_id = $request->matkul_id;
        $semester = $request->semester;

        $kelas = Kelas::findOrFail($kelas_id);
        $matkul = MataKuliah::findOrFail($matkul_id);
        $mahasiswa = $kelas->mahasiswa;

        return view('nilai.create', compact('kelas', 'matkul', 'mahasiswa', 'semester'));
    }

    public function store(Request $request) {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'matkul_id' => 'required|exists:mata_kuliah,id',
            'semester' => 'required|string',
            'tugas' => 'required|array',
            'tugas.*' => 'nullable|numeric|min:0|max:100',
            'uts' => 'required|array',
            'uts.*' => 'nullable|numeric|min:0|max:100',
            'uas' => 'required|array',
            'uas.*' => 'nullable|numeric|min:0|max:100',
        ]);

        $semesterFull = $request->semester;
        $mahasiswaIds = array_unique(array_merge(
            array_keys($request->tugas ?? []),
            array_keys($request->uts ?? []),
            array_keys($request->uas ?? [])
        ));

        foreach ($mahasiswaIds as $mahasiswa_id) {
            $tugas = $request->tugas[$mahasiswa_id] ?? null;
            $uts = $request->uts[$mahasiswa_id] ?? null;
            $uas = $request->uas[$mahasiswa_id] ?? null;

            // Only save if at least one value is not null
            if ($tugas || $uts || $uas) {
                $nilaiAkhir = null;
                if ($tugas && $uts && $uas) {
                    $nilaiAkhir = ($tugas + $uts + $uas) / 3;
                }

                Nilai::updateOrCreate(
                    [
                        'kelas_id' => $request->kelas_id,
                        'matkul_id' => $request->matkul_id,
                        'mahasiswa_id' => $mahasiswa_id,
                        'semester' => $semesterFull,
                    ],
                    [
                        'nilai_tugas' => $tugas,
                        'nilai_uts' => $uts,
                        'nilai_uas' => $uas,
                        'nilai_akhir' => $nilaiAkhir,
                    ]
                );
            }
        }

        return redirect()->route('nilai.index')->with('success', 'Nilai berhasil disimpan');
    }

    public function edit($id) {
        $nilai = Nilai::findOrFail($id);
        $mahasiswa = Mahasiswa::all();
        $mataKuliah = MataKuliah::all();
        return view('nilai.edit', compact('nilai', 'mahasiswa', 'mataKuliah'));
    }

    public function update(Request $request, $id) {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswa,id',
            'matkul_id' => 'required|exists:mata_kuliah,id',
            'nilai_tugas' => 'nullable|numeric|min:0|max:100',
            'nilai_uts' => 'nullable|numeric|min:0|max:100',
            'nilai_uas' => 'nullable|numeric|min:0|max:100',
            'semester' => 'required|string',
        ]);

        $nilai = Nilai::findOrFail($id);
        
        $nilaiAkhir = null;
        if ($request->nilai_tugas && $request->nilai_uts && $request->nilai_uas) {
            $nilaiAkhir = ($request->nilai_tugas + $request->nilai_uts + $request->nilai_uas) / 3;
        }

        $nilai->update([
            'mahasiswa_id' => $request->mahasiswa_id,
            'matkul_id' => $request->matkul_id,
            'nilai_tugas' => $request->nilai_tugas,
            'nilai_uts' => $request->nilai_uts,
            'nilai_uas' => $request->nilai_uas,
            'nilai_akhir' => $nilaiAkhir,
            'semester' => $request->semester,
        ]);

        return redirect()->route('nilai.index')->with('success', 'Nilai berhasil diperbarui');
    }

    public function destroy($id) {
        Nilai::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Nilai berhasil dihapus');
    }
}