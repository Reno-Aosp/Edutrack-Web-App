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
            $totalAbsensi = Absensi::where('mahasiswa_id', $n->mahasiswa_id)
                            ->where('matkul_id', $n->matkul_id)
                            ->count();
            $hadir = Absensi::where('mahasiswa_id', $n->mahasiswa_id)
                            ->where('matkul_id', $n->matkul_id)
                            ->where('status', 'hadir')
                            ->count();

            return [
                'matkul'          => $n->mataKuliah->nama ?? '-',
                'kode'            => $n->mataKuliah->kode ?? '-',
                'sks'             => $n->mataKuliah->sks ?? 0,
                'nilai_tugas'     => (float) ($n->nilai_tugas ?? 0),
                'nilai_uts'       => (float) ($n->nilai_uts ?? 0),
                'nilai_uas'       => (float) ($n->nilai_uas ?? 0),
                'nilai_akhir'     => (float) ($n->nilai_akhir ?? 0),
                'grade'           => $this->getGrade($n->nilai_akhir),
                'semester'        => $n->semester,
                'hadir'           => $hadir,
                'total_pertemuan' => $totalAbsensi,
            ];
        });

        $totalSks   = $nilai->sum('sks');
        $totalBobot = $nilai->sum(fn($n) => ($n['nilai_akhir'] / 100 * 4) * $n['sks']);
        $ipk        = $totalSks > 0 ? round($totalBobot / $totalSks, 2) : 0;

        return response()->json([
            'mahasiswa' => [
                'nama'     => $mahasiswa->nama ?? $user->name,
                'nim'      => $mahasiswa->nim,
                'prodi'    => $mahasiswa->prodi,
                'angkatan' => $mahasiswa->angkatan,
            ],
            'semester'  => $semester ?? 'Semua',
            'nilai'     => $nilai,
            'total_sks' => $totalSks,
            'ipk'       => $ipk,
        ]);
    }

    private function getGrade($nilai) {
        if (!$nilai) return 'E';
        if ($nilai >= 85) return 'A';
        if ($nilai >= 75) return 'B';
        if ($nilai >= 65) return 'C';
        if ($nilai >= 55) return 'D';
        return 'E';
    }
}