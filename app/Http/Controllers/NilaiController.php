<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use Illuminate\Http\Request;

class NilaiController extends Controller {

    public function index(Request $request) {
        $user = $request->user();
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa) {
            return response()->json(['data' => []]);
        }

        $nilai = Nilai::with(['mataKuliah'])
                    ->where('mahasiswa_id', $mahasiswa->id)
                    ->get();

        return response()->json(['data' => $nilai]);
    }

    public function store(Request $request) {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswa,id',
            'matkul_id'    => 'required|exists:mata_kuliah,id',
            'nilai_tugas'  => 'required|numeric|min:0|max:100',
            'nilai_uts'    => 'required|numeric|min:0|max:100',
            'nilai_uas'    => 'required|numeric|min:0|max:100',
            'semester'     => 'required',
        ]);

        $nilaiAkhir = ($request->nilai_tugas * 0.3) +
                      ($request->nilai_uts * 0.3) +
                      ($request->nilai_uas * 0.4);

        $nilai = Nilai::create([
            'mahasiswa_id' => $request->mahasiswa_id,
            'matkul_id'    => $request->matkul_id,
            'nilai_tugas'  => $request->nilai_tugas,
            'nilai_uts'    => $request->nilai_uts,
            'nilai_uas'    => $request->nilai_uas,
            'nilai_akhir'  => $nilaiAkhir,
            'semester'     => $request->semester,
        ]);

        return response()->json($nilai, 201);
    }

    public function update(Request $request, $id) {
        $nilai = Nilai::findOrFail($id);
        $nilai->update($request->all());
        return response()->json($nilai);
    }

    public function destroy($id) {
        Nilai::findOrFail($id)->delete();
        return response()->json(['message' => 'Nilai berhasil dihapus']);
    }
}