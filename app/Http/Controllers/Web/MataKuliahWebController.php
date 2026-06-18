<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Models\Dosen;
use Illuminate\Http\Request;

class MataKuliahWebController extends Controller {

    public function index() {
        $mataKuliah = MataKuliah::with('dosen.user')->get();
        return view('matakuliah.index', compact('mataKuliah'));
    }

    public function create() {
        $dosen = Dosen::with('user')->get();
        return view('matakuliah.create', compact('dosen'));
    }

    public function store(Request $request) {
        $request->validate([
            'nama'     => 'required|string|max:100',
            'kode'     => 'required|string|max:20|unique:mata_kuliah',
            'sks'      => 'required|integer|min:1|max:6',
            'dosen_id' => 'nullable|exists:dosen,id',
        ]);

        MataKuliah::create($request->all());

        return redirect()->route('matakuliah.index')
            ->with('success', 'Mata kuliah berhasil ditambahkan!');
    }

    public function edit($id) {
        $mataKuliah = MataKuliah::findOrFail($id);
        $dosen = Dosen::with('user')->get();
        return view('matakuliah.edit', compact('mataKuliah', 'dosen'));
    }

    public function update(Request $request, $id) {
        $mataKuliah = MataKuliah::findOrFail($id);
        $mataKuliah->update($request->all());
        return redirect()->route('matakuliah.index')
            ->with('success', 'Mata kuliah berhasil diupdate!');
    }

    public function destroy($id) {
        MataKuliah::findOrFail($id)->delete();
        return redirect()->route('matakuliah.index')
            ->with('success', 'Mata kuliah berhasil dihapus!');
    }
}