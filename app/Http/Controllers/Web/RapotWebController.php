<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Nilai;
use App\Models\Mahasiswa;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RapotWebController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // ── DOSEN ────────────────────────────────────────────────
        if ($user->role === 'dosen') {
            return $this->rapotDosen($request, $user);
        }

        // ── ADMIN ────────────────────────────────────────────────
        return $this->rapotAdmin($request);
    }

    // ─────────────────────────────────────────────────────────────
    // RAPOT DOSEN
    // Dosen hanya bisa lihat rapor mahasiswa di kelas & matkul
    // yang dia ampu
    // ─────────────────────────────────────────────────────────────
    private function rapotDosen(Request $request, $user)
    {
        $dosen     = $user->dosen;
        $matkulIds = $dosen
            ? MataKuliah::where('dosen_id', $dosen->id)->pluck('id')
            : collect();

        // Kelas unik yang diajar dosen ini
        $allKelas = Kelas::whereHas('mataKuliah', function ($q) use ($matkulIds) {
            $q->whereIn('mata_kuliah.id', $matkulIds);
        })->get();

        $kelasId     = $request->query('kelas_id');
        $mahasiswaId = $request->query('mahasiswa_id');
        $allMahasiswa = collect();
        $mahasiswa   = null;

        // Step 1: pilih kelas
        if (!$kelasId) {
            return view('rapot.index', [
                'allKelas'     => $allKelas,
                'allMahasiswa' => $allMahasiswa,
                'mahasiswa'    => null,
                'semuaSemester'=> [],
                'semester'     => null,
                'nilaiData'    => collect(),
                'ipk'          => 0,
                'totalSks'     => 0,
                'kelasId'      => null,
                'isDosen'      => true,
            ]);
        }

        // Validasi: kelas harus dari kelas yang diajar dosen
        if (!$allKelas->contains('id', (int)$kelasId)) {
            return redirect()->route('rapot.index')
                ->with('error', 'Kelas tidak tersedia untuk Anda.');
        }

        $kelas        = Kelas::findOrFail($kelasId);
        $allMahasiswa = $kelas->mahasiswa;

        // Step 2: pilih mahasiswa
        if (!$mahasiswaId) {
            return view('rapot.index', [
                'allKelas'     => $allKelas,
                'allMahasiswa' => $allMahasiswa,
                'mahasiswa'    => null,
                'semuaSemester'=> [],
                'semester'     => null,
                'nilaiData'    => collect(),
                'ipk'          => 0,
                'totalSks'     => 0,
                'kelasId'      => $kelasId,
                'isDosen'      => true,
            ]);
        }

        $mahasiswa = Mahasiswa::find($mahasiswaId);
        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }

        // Semester dari nilai matkul yang diajar dosen ini saja
        $semuaSemester = Nilai::where('mahasiswa_id', $mahasiswa->id)
            ->whereIn('matkul_id', $matkulIds)
            ->distinct()
            ->pluck('semester')
            ->sort()
            ->values()
            ->toArray();

        $semester   = $request->semester;
        $nilaiData  = collect();
        $totalSks   = 0;
        $totalBobot = 0;

        if ($semester) {
            $nilaiData = Nilai::with(['mataKuliah', 'kelas'])
                ->where('mahasiswa_id', $mahasiswa->id)
                ->whereIn('matkul_id', $matkulIds) // hanya matkul yang diajar dosen ini
                ->where('semester', $semester)
                ->get()
                ->map(function ($n) {
                    $hadir = Absensi::where('mahasiswa_id', $n->mahasiswa_id)
                        ->where('matkul_id', $n->matkul_id)
                        ->where('status', 'hadir')
                        ->count();
                    $totalAbsensi = Absensi::where('mahasiswa_id', $n->mahasiswa_id)
                        ->where('matkul_id', $n->matkul_id)
                        ->count();
                    return [
                        'matkul_nama'    => $n->mataKuliah->nama ?? '-',
                        'matkul_kode'    => $n->mataKuliah->kode ?? '-',
                        'sks'            => $n->mataKuliah->sks ?? 0,
                        'nilai_tugas'    => $n->nilai_tugas ?? '-',
                        'nilai_uts'      => $n->nilai_uts ?? '-',
                        'nilai_uas'      => $n->nilai_uas ?? '-',
                        'nilai_akhir'    => $n->nilai_akhir ?? '-',
                        'grade'          => $this->getGrade($n->nilai_akhir),
                        'hadir'          => $hadir,
                        'total_pertemuan'=> $totalAbsensi,
                        'presensi_persen'=> $totalAbsensi > 0
                            ? round(($hadir / $totalAbsensi) * 100, 1) : 0,
                    ];
                });

            foreach ($nilaiData as $nilai) {
                if ($nilai['sks'] > 0 && $nilai['nilai_akhir'] !== '-') {
                    $totalSks   += $nilai['sks'];
                    $totalBobot += ($nilai['nilai_akhir'] / 100 * 4) * $nilai['sks'];
                }
            }
        }

        $ipk = $totalSks > 0 ? round($totalBobot / $totalSks, 2) : 0;

        return view('rapot.index', compact(
            'mahasiswa', 'allMahasiswa', 'allKelas',
            'kelasId', 'semuaSemester', 'semester',
            'nilaiData', 'ipk', 'totalSks'
        ) + ['isDosen' => true]);
    }

    // ─────────────────────────────────────────────────────────────
    // RAPOT ADMIN
    // ─────────────────────────────────────────────────────────────
    private function rapotAdmin(Request $request)
    {
        $allKelas     = Kelas::all();
        $allMahasiswa = collect();
        $mahasiswa    = null;
        $kelasId      = $request->query('kelas_id');
        $mahasiswaId  = $request->query('mahasiswa_id');

        if (!$kelasId) {
            return view('rapot.index', [
                'allKelas'     => $allKelas,
                'allMahasiswa' => $allMahasiswa,
                'mahasiswa'    => null,
                'semuaSemester'=> [],
                'semester'     => null,
                'nilaiData'    => collect(),
                'ipk'          => 0,
                'totalSks'     => 0,
                'kelasId'      => null,
                'isDosen'      => false,
            ]);
        }

        $kelas = Kelas::find($kelasId);
        if (!$kelas) {
            return redirect()->back()->with('error', 'Kelas tidak ditemukan');
        }

        $allMahasiswa = $kelas->mahasiswa;

        if (!$mahasiswaId) {
            return view('rapot.index', [
                'allKelas'     => $allKelas,
                'allMahasiswa' => $allMahasiswa,
                'mahasiswa'    => null,
                'semuaSemester'=> [],
                'semester'     => null,
                'nilaiData'    => collect(),
                'ipk'          => 0,
                'totalSks'     => 0,
                'kelasId'      => $kelasId,
                'isDosen'      => false,
            ]);
        }

        $mahasiswa = Mahasiswa::find($mahasiswaId);
        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan');
        }

        $semuaSemester = Nilai::where('mahasiswa_id', $mahasiswa->id)
            ->distinct()->pluck('semester')->sort()->values()->toArray();

        $semester   = $request->semester;
        $nilaiData  = collect();
        $totalSks   = 0;
        $totalBobot = 0;

        if ($semester) {
            $nilaiData = Nilai::with(['mataKuliah', 'kelas'])
                ->where('mahasiswa_id', $mahasiswa->id)
                ->where('semester', $semester)
                ->get()
                ->map(function ($n) {
                    $hadir = Absensi::where('mahasiswa_id', $n->mahasiswa_id)
                        ->where('matkul_id', $n->matkul_id)
                        ->where('status', 'hadir')->count();
                    $totalAbsensi = Absensi::where('mahasiswa_id', $n->mahasiswa_id)
                        ->where('matkul_id', $n->matkul_id)->count();
                    return [
                        'matkul_nama'    => $n->mataKuliah->nama ?? '-',
                        'matkul_kode'    => $n->mataKuliah->kode ?? '-',
                        'sks'            => $n->mataKuliah->sks ?? 0,
                        'nilai_tugas'    => $n->nilai_tugas ?? '-',
                        'nilai_uts'      => $n->nilai_uts ?? '-',
                        'nilai_uas'      => $n->nilai_uas ?? '-',
                        'nilai_akhir'    => $n->nilai_akhir ?? '-',
                        'grade'          => $this->getGrade($n->nilai_akhir),
                        'hadir'          => $hadir,
                        'total_pertemuan'=> $totalAbsensi,
                        'presensi_persen'=> $totalAbsensi > 0
                            ? round(($hadir / $totalAbsensi) * 100, 1) : 0,
                    ];
                });

            foreach ($nilaiData as $nilai) {
                if ($nilai['sks'] > 0 && $nilai['nilai_akhir'] !== '-') {
                    $totalSks   += $nilai['sks'];
                    $totalBobot += ($nilai['nilai_akhir'] / 100 * 4) * $nilai['sks'];
                }
            }
        }

        $ipk = $totalSks > 0 ? round($totalBobot / $totalSks, 2) : 0;

        return view('rapot.index', compact(
            'mahasiswa', 'allMahasiswa', 'allKelas',
            'kelasId', 'semuaSemester', 'semester',
            'nilaiData', 'ipk', 'totalSks'
        ) + ['isDosen' => false]);
    }

    private function getGrade($nilai)
    {
        if (!$nilai || $nilai === '-') return '-';
        if ($nilai >= 85) return 'A';
        if ($nilai >= 75) return 'B';
        if ($nilai >= 65) return 'C';
        if ($nilai >= 55) return 'D';
        return 'E';
    }
}
