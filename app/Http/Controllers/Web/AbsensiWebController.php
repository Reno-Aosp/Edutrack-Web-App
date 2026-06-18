<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use Illuminate\Http\Request;

class AbsensiWebController extends Controller {

    public function index() {
        $absensi = Absensi::with('mahasiswa.user', 'mataKuliah')->get();
        return view('absensi.index', compact('absensi'));
    }

    public function create() {
        $mahasiswa = Mahasiswa::with('user')->get();
        $mataKuliah = MataKuliah::all();
        return view('absensi.create', compact('mahasiswa', 'mataKuliah'));
    }

    public function store(Request $request) {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswa,id',
            'matkul_id'    => 'required|exists:mata_kuliah,id',
            'tanggal'      => 'required|date',
            'status'       => 'required|in:hadir,sakit,izin,alpha',
            'keterangan'   => 'nullable|string',
        ]);

        Absensi::create($request->all());

        return redirect()->route('absensi.index')
            ->with('success', 'Absensi berhasil ditambahkan!');
    }

    public function edit($id) {
        $absensi = Absensi::findOrFail($id);
        $mahasiswa = Mahasiswa::with('user')->get();
        $mataKuliah = MataKuliah::all();
        return view('absensi.edit', compact('absensi', 'mahasiswa', 'mataKuliah'));
    }

    public function update(Request $request, $id) {
        $absensi = Absensi::findOrFail($id);
        $absensi->update($request->all());

        return redirect()->route('absensi.index')
            ->with('success', 'Absensi berhasil diupdate!');
    }

    public function destroy($id) {
        Absensi::findOrFail($id)->delete();
        return redirect()->route('absensi.index')
            ->with('success', 'Absensi berhasil dihapus!');
    }
}