<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use Illuminate\Http\Request;

class NilaiController extends Controller {

    public function index($mahasiswa_id) {
        $nilai = Nilai::with('mataKuliah')
                    ->where('mahasiswa_id', $mahasiswa_id)
                    ->get();
        return response()->json($nilai);
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

        $nilai = Nilai::create($request->all());
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