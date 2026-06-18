<?php

namespace App\Http\Controllers;

use App\Models\MataKuliah;
use Illuminate\Http\Request;

class MataKuliahController extends Controller {

    public function index(Request $request) {
        $query = MataKuliah::with('dosen');
        
        // Filter by mahasiswa_id jika ada parameter
        if ($request->has('mahasiswa_id')) {
            $mahasiswaId = $request->query('mahasiswa_id');
            $query->whereHas('kelas', function($q) use ($mahasiswaId) {
                $q->whereHas('mahasiswa', function($q2) use ($mahasiswaId) {
                    $q2->where('mahasiswa.id', $mahasiswaId);
                });
            });
        }
        
        $mataKuliah = $query->get();
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