<?php

namespace App\Http\Controllers;

use App\Models\MataKuliah;
use Illuminate\Http\Request;

class MataKuliahController extends Controller {

    public function index() {
        $mataKuliah = MataKuliah::with('dosen')->get();
        return response()->json($mataKuliah);
    }

    public function store(Request $request) {
        $request->validate([
            'nama'     => 'required|string|max:100',
            'kode'     => 'required|string|max:20|unique:mata_kuliah',
            'sks'      => 'required|integer|min:1|max:6',
            'dosen_id' => 'nullable|exists:dosen,id',
        ]);

        $mataKuliah = MataKuliah::create($request->all());
        return response()->json($mataKuliah, 201);
    }

    public function update(Request $request, $id) {
        $mataKuliah = MataKuliah::findOrFail($id);
        $mataKuliah->update($request->all());
        return response()->json($mataKuliah);
    }

    public function destroy($id) {
        MataKuliah::findOrFail($id)->delete();
        return response()->json(['message' => 'Mata kuliah berhasil dihapus']);
    }
}