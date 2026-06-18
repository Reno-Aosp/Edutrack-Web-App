<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\Dosen;
use Illuminate\Http\Request;

class JadwalWebController extends Controller {

    public function index(Request $request) {
        $semuaKelas = Kelas::all();

        if ($request->kelas_id) {
            $kelas  = Kelas::findOrFail($request->kelas_id);
            $jadwal = Jadwal::with(['mataKuliah', 'dosen.user'])
                        ->where('kelas_id', $request->kelas_id)
                        ->orderByRaw("FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')")
                        ->orderBy('jam_mulai')
                        ->get();
            return view('jadwal.index', compact('kelas', 'jadwal', 'semuaKelas'));
        }

        return view('jadwal.index', compact('semuaKelas'));
    }

    public function create(Request $request) {
        $kelas_id = $request->kelas_id;
        $kelas    = Kelas::findOrFail($kelas_id);
        $matkul   = $kelas->mataKuliah;
        $dosen    = Dosen::with('user')->get();
        return view('jadwal.create', compact('kelas', 'matkul', 'dosen'));
    }

    public function store(Request $request) {
        $request->validate([
            'kelas_id'    => 'required|exists:kelas,id',
            'matkul_id'   => 'required|exists:mata_kuliah,id',
            'dosen_id'    => 'nullable|exists:dosen,id',
            'hari'        => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required',
            'ruangan'     => 'nullable|string|max:50',
        ]);

        Jadwal::create($request->all());

        return redirect()->route('jadwal.index', ['kelas_id' => $request->kelas_id])
            ->with('success', 'Jadwal berhasil ditambahkan!');
    }

    public function edit($id) {
        $jadwal = Jadwal::findOrFail($id);
        $kelas  = Kelas::findOrFail($jadwal->kelas_id);
        $matkul = $kelas->mataKuliah;
        $dosen  = Dosen::with('user')->get();
        return view('jadwal.edit', compact('jadwal', 'kelas', 'matkul', 'dosen'));
    }

    public function update(Request $request, $id) {
        $jadwal = Jadwal::findOrFail($id);
        $jadwal->update($request->all());
        return redirect()->route('jadwal.index', ['kelas_id' => $jadwal->kelas_id])
            ->with('success', 'Jadwal berhasil diupdate!');
    }

    public function destroy($id) {
        $jadwal   = Jadwal::findOrFail($id);
        $kelas_id = $jadwal->kelas_id;
        $jadwal->delete();
        return redirect()->route('jadwal.index', ['kelas_id' => $kelas_id])
            ->with('success', 'Jadwal berhasil dihapus!');
    }
}