<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Nilai;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Kelas;
use Illuminate\Http\Request;

class NilaiWebController extends Controller {

    public function index(Request $request) {
        if ($request->kelas_id) {
            $kelas = Kelas::findOrFail($request->kelas_id);
            $nilai = Nilai::with(['mahasiswa', 'mataKuliah'])
                ->where('kelas_id', $request->kelas_id)
                ->get();
            return view('nilai.index', compact('kelas', 'nilai'));
        }

        $semuaKelas = Kelas::withCount('mahasiswa')->get();
        return view('nilai.index', compact('semuaKelas'));
    }

    public function create(Request $request) {
        $kelas_id = $request->kelas_id;
        $kelas = Kelas::with('mahasiswa')->findOrFail($kelas_id);
        $mahasiswa = $kelas->mahasiswa;
        $mataKuliah = MataKuliah::all();
        return view('nilai.create', compact('mahasiswa', 'mataKuliah', 'kelas'));
    }

    public function store(Request $request) {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswa,id',
            'matkul_id'    => 'required|exists:mata_kuliah,id',
            'nilai_tugas'  => 'required|numeric|min:0|max:100',
            'nilai_uts'    => 'required|numeric|min:0|max:100',
            'nilai_uas'    => 'required|numeric|min:0|max:100',
            'semester'     => 'required',
            'kelas_id'     => 'required|exists:kelas,id',
        ]);

        Nilai::create($request->all());

        return redirect()->route('nilai.index', ['kelas_id' => $request->kelas_id])
            ->with('success', 'Nilai berhasil ditambahkan!');
    }

    public function edit($id) {
        $nilai = Nilai::findOrFail($id);
        $kelas = Kelas::with('mahasiswa')->findOrFail($nilai->kelas_id);
        $mahasiswa = $kelas->mahasiswa;
        $mataKuliah = MataKuliah::all();
        return view('nilai.edit', compact('nilai', 'mahasiswa', 'mataKuliah', 'kelas'));
    }

    public function update(Request $request, $id) {
        $nilai = Nilai::findOrFail($id);
        $nilai->update($request->all());

        return redirect()->route('nilai.index', ['kelas_id' => $nilai->kelas_id])
            ->with('success', 'Nilai berhasil diupdate!');
    }

    public function destroy($id) {
        $nilai = Nilai::findOrFail($id);
        $kelas_id = $nilai->kelas_id;
        $nilai->delete();
        return redirect()->route('nilai.index', ['kelas_id' => $kelas_id])
            ->with('success', 'Nilai berhasil dihapus!');
    }
}