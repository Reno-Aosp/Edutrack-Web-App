<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use Illuminate\Http\Request;

class AbsensiController extends Controller {

    public function index($mahasiswa_id) {
        $absensi = Absensi::with('mataKuliah')
                    ->where('mahasiswa_id', $mahasiswa_id)
                    ->get();
        return response()->json($absensi);
    }

    public function store(Request $request) {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswa,id',
            'matkul_id'    => 'required|exists:mata_kuliah,id',
            'tanggal'      => 'required|date',
            'status'       => 'required|in:hadir,sakit,izin,alpha',
        ]);

        $absensi = Absensi::create($request->all());
        return response()->json($absensi, 201);
    }

    public function rekap($mahasiswa_id) {
        $rekap = Absensi::selectRaw('status, COUNT(*) as total')
                    ->where('mahasiswa_id', $mahasiswa_id)
                    ->groupBy('status')
                    ->get();
        return response()->json($rekap);
    }

    public function destroy($id) {
        Absensi::findOrFail($id)->delete();
        return response()->json(['message' => 'Absensi berhasil dihapus']);
    }
}