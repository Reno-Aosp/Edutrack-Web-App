<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;

class MahasiswaController extends Controller {

    public function index() {
        $mahasiswa = Mahasiswa::with('user')->get();
        return response()->json($mahasiswa);
    }

    public function show($id) {
        $mahasiswa = Mahasiswa::with('user', 'nilai', 'absensi')
                        ->findOrFail($id);
        return response()->json($mahasiswa);
    }

    public function store(Request $request) {
        $request->validate([
            'user_id'  => 'required|exists:users,id',
            'nim'      => 'required|string|max:20|unique:mahasiswa',
            'prodi'    => 'required|string|max:50',
            'angkatan' => 'nullable|string|max:10',
        ]);

        $mahasiswa = Mahasiswa::create($request->all());
        return response()->json($mahasiswa, 201);
    }

    public function update(Request $request, $id) {
        $mahasiswa = Mahasiswa::findOrFail($id);
        $mahasiswa->update($request->all());
        return response()->json($mahasiswa);
    }

    public function destroy($id) {
        Mahasiswa::findOrFail($id)->delete();
        return response()->json(['message' => 'Mahasiswa berhasil dihapus']);
    }
}