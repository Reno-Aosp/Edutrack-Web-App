<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use Illuminate\Http\Request;

class KelasWebController extends Controller {

    public function index() {
        $kelas = Kelas::withCount('mahasiswa')->get();
        return view('kelas.index', compact('kelas'));
    }

    public function create() {
        return view('kelas.create');
    }

    public function store(Request $request) {
        $request->validate([
            'nama_kelas' => 'required|string|max:100',
            'prodi'      => 'required|string|max:100',
            'angkatan'   => 'required|integer',
            'semester'   => 'required|integer|min:1|max:14',
        ]);

        Kelas::create($request->all());

        return redirect()->route('kelas.index')
            ->with('success', 'Kelas berhasil ditambahkan!');
    }

    public function show($id) {
        $kelas = Kelas::with(['mahasiswa', 'mataKuliah'])->findOrFail($id);
        $semuaMahasiswa = Mahasiswa::all();
        $semuaMataKuliah = MataKuliah::all();
        return view('kelas.show', compact('kelas', 'semuaMahasiswa', 'semuaMataKuliah'));
    }

    public function edit($id) {
        $kelas = Kelas::findOrFail($id);
        return view('kelas.edit', compact('kelas'));
    }

    public function update(Request $request, $id) {
        $kelas = Kelas::findOrFail($id);
        $kelas->update($request->all());
        return redirect()->route('kelas.index')
            ->with('success', 'Kelas berhasil diupdate!');
    }

    public function destroy($id) {
        Kelas::findOrFail($id)->delete();
        return redirect()->route('kelas.index')
            ->with('success', 'Kelas berhasil dihapus!');
    }

    public function tambahMahasiswa(Request $request, $id) {
        $kelas = Kelas::findOrFail($id);
        $kelas->mahasiswa()->syncWithoutDetaching($request->mahasiswa_ids);
        return redirect()->route('kelas.show', $id)
            ->with('success', 'Mahasiswa berhasil ditambahkan ke kelas!');
    }

    public function hapusMahasiswa($kelas_id, $mahasiswa_id) {
        $kelas = Kelas::findOrFail($kelas_id);
        $kelas->mahasiswa()->detach($mahasiswa_id);
        return redirect()->route('kelas.show', $kelas_id)
            ->with('success', 'Mahasiswa berhasil dihapus dari kelas!');
    }

    public function assignMatkul(Request $request, $id) {
        $kelas = Kelas::findOrFail($id);
        $kelas->mataKuliah()->sync($request->matkul_ids ?? []);
        return redirect()->route('kelas.show', $id)
            ->with('success', 'Mata kuliah berhasil diupdate!');
    }
}