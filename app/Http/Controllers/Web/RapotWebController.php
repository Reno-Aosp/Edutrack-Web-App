<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Nilai;
use App\Models\Mahasiswa;
use App\Models\Kelas;
use App\Models\Absensi;
use Illuminate\Http\Request;

class RapotWebController extends Controller {
    
    public function index(Request $request) {
        $user = $request->user();
        
        // Jika Admin
        if ($user->role === 'admin') {
            $allKelas = Kelas::all();
            $allMahasiswa = collect();
            $mahasiswa = null;
            
            $kelasId = $request->query('kelas_id');
            $mahasiswaId = $request->query('mahasiswa_id');
            
            // Jika kelas belum dipilih
            if (!$kelasId) {
                return view('rapot.index', [
                    'allKelas' => $allKelas,
                    'allMahasiswa' => $allMahasiswa,
                    'mahasiswa' => null,
                    'semuaSemester' => [],
                    'semester' => null,
                    'nilaiData' => collect(),
                    'ipk' => 0,
                    'totalSks' => 0,
                    'kelasId' => null
                ]);
            }
            
            // Jika kelas dipilih, ambil mahasiswa dari kelas tersebut
            $kelas = Kelas::find($kelasId);
            if (!$kelas) {
                return redirect()->back()->with('error', 'Kelas tidak ditemukan');
            }
            
            $allMahasiswa = $kelas->mahasiswa;
            
            // Jika mahasiswa belum dipilih
            if (!$mahasiswaId) {
                return view('rapot.index', [
                    'allKelas' => $allKelas,
                    'allMahasiswa' => $allMahasiswa,
                    'mahasiswa' => null,
                    'semuaSemester' => [],
                    'semester' => null,
                    'nilaiData' => collect(),
                    'ipk' => 0,
                    'totalSks' => 0,
                    'kelasId' => $kelasId
                ]);
            }
            
            // Mahasiswa dipilih
            $mahasiswa = Mahasiswa::find($mahasiswaId);
            if (!$mahasiswa) {
                return redirect()->back()->with('error', 'Data mahasiswa tidak ditemukan');
            }
        } else {
            // Jika Mahasiswa, langsung tampilkan rapor mereka
            $mahasiswa = $user->mahasiswa;
            if (!$mahasiswa) {
                return redirect()->back()->with('error', 'Data mahasiswa tidak ditemukan');
            }
            $allKelas = collect();
            $allMahasiswa = collect();
            $kelasId = null;
        }

        // Get all unique semesters
        $semuaSemester = Nilai::where('mahasiswa_id', $mahasiswa->id)
            ->distinct()
            ->pluck('semester')
            ->sort()
            ->values()
            ->toArray();

        $semester = $request->semester;
        $nilaiData = [];
        $totalSks = 0;
        $totalBobot = 0;

        if ($semester) {
            $nilaiData = Nilai::with(['mataKuliah', 'kelas'])
                ->where('mahasiswa_id', $mahasiswa->id)
                ->where('semester', $semester)
                ->get()
                ->map(function ($n) {
                    $hadir = Absensi::where('mahasiswa_id', $n->mahasiswa_id)
                        ->where('matkul_id', $n->matkul_id)
                        ->where('semester', $n->semester)
                        ->where('status', 'hadir')
                        ->count();

                    $totalAbsensi = Absensi::where('mahasiswa_id', $n->mahasiswa_id)
                        ->where('matkul_id', $n->matkul_id)
                        ->where('semester', $n->semester)
                        ->count();

                    return [
                        'matkul_nama' => $n->mataKuliah->nama ?? '-',
                        'matkul_kode' => $n->mataKuliah->kode ?? '-',
                        'sks' => $n->mataKuliah->sks ?? 0,
                        'nilai_tugas' => $n->nilai_tugas ?? '-',
                        'nilai_uts' => $n->nilai_uts ?? '-',
                        'nilai_uas' => $n->nilai_uas ?? '-',
                        'nilai_akhir' => $n->nilai_akhir ?? '-',
                        'grade' => $this->getGrade($n->nilai_akhir),
                        'hadir' => $hadir,
                        'total_pertemuan' => $totalAbsensi,
                        'presensi_persen' => $totalAbsensi > 0 ? round(($hadir / $totalAbsensi) * 100, 1) : 0,
                    ];
                });

            // Hitung IPK dan total SKS
            foreach ($nilaiData as $nilai) {
                if ($nilai['sks'] > 0 && $nilai['nilai_akhir'] !== '-') {
                    $totalSks += $nilai['sks'];
                    $bobotNilai = ($nilai['nilai_akhir'] / 100) * 4;
                    $totalBobot += $bobotNilai * $nilai['sks'];
                }
            }
        }

        $ipk = $totalSks > 0 ? round($totalBobot / $totalSks, 2) : 0;

        return view('rapot.index', compact(
            'mahasiswa', 
            'allMahasiswa',
            'allKelas',
            'kelasId',
            'semuaSemester', 
            'semester', 
            'nilaiData', 
            'ipk', 
            'totalSks'
        ));
    }

    public function show($semester) {
        $user = auth()->user();
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Data mahasiswa tidak ditemukan');
        }

        // Verify semester exists for this mahasiswa
        $exists = Nilai::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester', $semester)
            ->exists();

        if (!$exists) {
            return redirect()->route('rapot.index')->with('error', 'Data semester tidak ditemukan');
        }

        return redirect()->route('rapot.index', ['semester' => $semester]);
    }

    private function getGrade($nilai) {
        if (!$nilai || $nilai === '-') return '-';
        if ($nilai >= 85) return 'A';
        if ($nilai >= 75) return 'B';
        if ($nilai >= 65) return 'C';
        if ($nilai >= 55) return 'D';
        return 'E';
    }

    private function getGradeColor($grade) {
        return match($grade) {
            'A' => 'success',
            'B' => 'info',
            'C' => 'warning',
            'D', 'E' => 'danger',
            default => 'secondary',
        };
    }
}
