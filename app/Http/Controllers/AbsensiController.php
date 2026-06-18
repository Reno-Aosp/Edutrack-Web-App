<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;

class AbsensiController extends Controller {

    public function index(Request $request) {
        $user = $request->user();
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa) {
            return response()->json(['data' => []]);
        }

        $absensi = Absensi::with(['mataKuliah'])
                    ->where('mahasiswa_id', $mahasiswa->id)
                    ->orderBy('tanggal', 'desc')
                    ->get();

        return response()->json(['data' => $absensi]);
    }

    public function store(Request $request) {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswa,id',
            'matkul_id'    => 'required|exists:mata_kuliah,id',
            'tanggal'      => 'required|date',
            'status'       => 'required|in:hadir,sakit,izin,alpha',
            'keterangan'   => 'nullable|string',
            'kelas_id'     => 'nullable|exists:kelas,id',
        ]);

        // ✅ Jika kelas_id tidak dikirim, ambil dari kelas pertama mahasiswa
        $kelas_id = $request->kelas_id;
        if (!$kelas_id) {
            $mahasiswa = Mahasiswa::find($request->mahasiswa_id);
            $firstKelas = $mahasiswa->kelas()->first();
            $kelas_id = $firstKelas ? $firstKelas->id : null;
        }

        $absensi = Absensi::create([
            'mahasiswa_id' => $request->mahasiswa_id,
            'matkul_id'    => $request->matkul_id,
            'kelas_id'     => $kelas_id,
            'tanggal'      => $request->tanggal,
            'status'       => $request->status,
            'keterangan'   => $request->keterangan ?? null,
        ]);
        
        return response()->json(['success' => true, 'data' => $absensi], 201);
    }

    public function destroy($id) {
        Absensi::findOrFail($id)->delete();
        return response()->json(['message' => 'Absensi berhasil dihapus']);
    }
}