<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use App\Models\Absensi;
use Illuminate\Http\Request;

class RapotController extends Controller {
    public function index(Request $request) {
        $user      = $request->user();
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa) {
            return response()->json(['message' => 'Data mahasiswa tidak ditemukan'], 404);
        }

        $semester = $request->semester;

        $nilaiQuery = Nilai::with(['mataKuliah'])
                        ->where('mahasiswa_id', $mahasiswa->id);

        if ($semester) {
            $nilaiQuery->where('semester', $semester);
        }

        $nilai = $nilaiQuery->get()->map(function ($n) {
            // Hitung total absensi
            $totalAbsensi = Absensi::where('mahasiswa_id', $n->mahasiswa_id)
                            ->where('matkul_id', $n->matkul_id)
                            ->where('semester', $n->semester)
                            ->count();
            $hadir = Absensi::where('mahasiswa_id', $n->mahasiswa_id)
                            ->where('matkul_id', $n->matkul_id)
                            ->where('semester', $n->semester)
                            ->where('status', 'hadir')
                            ->count();

            return [
                'matkul'       => $n->mataKuliah->nama ?? '-',
                'kode'         => $n->mataKuliah->kode ?? '-',
                'sks'          => $n->mataKuliah->sks ?? 0,
                'nilai_tugas'  => $n->nilai_tugas,
                'nilai_uts'    => $n->nilai_uts,
                'nilai_uas'    => $n->nilai_uas,
                'nilai_akhir'  => $n->nilai_akhir,
                'grade'        => $this->getGrade($n->nilai_akhir),
                'semester'     => $n->semester,
                'hadir'        => $hadir,
                'total_pertemuan' => $totalAbsensi,
            ];
        });

        return response()->json([
            'mahasiswa' => [
                'nama'    => $mahasiswa->nama ?? $user->name,
                'nim'     => $mahasiswa->nim,
                'prodi'   => $mahasiswa->prodi,
                'angkatan' => $mahasiswa->angkatan,
            ],
            'semester' => $semester ?? 'Semua',
            'nilai'    => $nilai,
            'total_sks' => $nilai->sum('sks'),
            'ipk'       => $nilai->count() > 0 ? round($nilai->avg('nilai_akhir') / 25, 2) : 0,
        ]);
    }

    private function getGrade($nilai) {
        if ($nilai >= 85) return 'A';
        if ($nilai >= 75) return 'B';
        if ($nilai >= 65) return 'C';
        if ($nilai >= 55) return 'D';
        return 'E';
    }
}