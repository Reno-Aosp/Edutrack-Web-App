<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use Illuminate\Http\Request;

class JadwalController extends Controller {
    public function index(Request $request) {
        $user      = $request->user();
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa) {
            return response()->json(['message' => 'Data mahasiswa tidak ditemukan'], 404);
        }

        // Ambil kelas mahasiswa
        $kelasIds = $mahasiswa->kelas->pluck('id');

        $jadwal = Jadwal::with(['mataKuliah', 'dosen.user', 'kelas'])
                    ->whereIn('kelas_id', $kelasIds)
                    ->orderByRaw("FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')")
                    ->orderBy('jam_mulai')
                    ->get()
                    ->map(function ($j) {
                        return [
                            'id'          => $j->id,
                            'hari'        => $j->hari,
                            'jam_mulai'   => $j->jam_mulai,
                            'jam_selesai' => $j->jam_selesai,
                            'ruangan'     => $j->ruangan,
                            'matkul'      => $j->mataKuliah->nama ?? '-',
                            'kode'        => $j->mataKuliah->kode ?? '-',
                            'dosen'       => $j->dosen->user->name ?? '-',
                            'kelas'       => $j->kelas->nama_kelas ?? '-',
                        ];
                    });

        return response()->json($jadwal);
    }
}