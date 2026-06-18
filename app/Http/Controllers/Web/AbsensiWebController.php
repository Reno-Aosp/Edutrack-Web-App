<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\MataKuliah;
use Illuminate\Http\Request;

class AbsensiWebController extends Controller {

    public function index(Request $request) {
        $semuaKelas = Kelas::withCount('mahasiswa')->get();

        if ($request->kelas_id && $request->matkul_id) {
            $kelas = Kelas::findOrFail($request->kelas_id);
            $matkul = MataKuliah::findOrFail($request->matkul_id);
            
            // ✅ Ambil semua mahasiswa di kelas ini
            $mahasiswaIds = $kelas->mahasiswa()->pluck('mahasiswa.id')->toArray();
            
            // ✅ Query: absensi untuk matkul ini, dari mahasiswa di kelas ini
            $absensi = Absensi::with(['mahasiswa', 'mataKuliah'])
                        ->where('matkul_id', $request->matkul_id)
                        ->whereIn('mahasiswa_id', $mahasiswaIds)
                        ->orderBy('tanggal', 'desc')
                        ->get();
                        
            return view('absensi.index', compact('kelas', 'matkul', 'absensi', 'semuaKelas'));
        }

        if ($request->kelas_id) {
            $kelas = Kelas::findOrFail($request->kelas_id);
            $semuaMatkul = $kelas->mataKuliah;
            return view('absensi.index', compact('kelas', 'semuaMatkul', 'semuaKelas'));
        }

        return view('absensi.index', compact('semuaKelas'));
    }

    public function create(Request $request) {
        $kelas_id = $request->kelas_id;
        $matkul_id = $request->matkul_id;
        $kelas = Kelas::with('mahasiswa')->findOrFail($kelas_id);
        $mahasiswa = $kelas->mahasiswa;
        $matkul = MataKuliah::findOrFail($matkul_id);
        return view('absensi.create', compact('mahasiswa', 'matkul', 'kelas'));
    }

    public function store(Request $request) {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswa,id',
            'matkul_id'    => 'required|exists:mata_kuliah,id',
            'kelas_id'     => 'required|exists:kelas,id',
            'tanggal'      => 'required|date',
            'status'       => 'required|in:hadir,sakit,izin,alpha',
            'keterangan'   => 'nullable|string',
        ]);

        Absensi::create([
            'mahasiswa_id' => $request->mahasiswa_id,
            'matkul_id'    => $request->matkul_id,
            'kelas_id'     => $request->kelas_id,
            'tanggal'      => $request->tanggal,
            'status'       => $request->status,
            'keterangan'   => $request->keterangan,
        ]);

        return redirect()->route('absensi.index', [
            'kelas_id' => $request->kelas_id,
            'matkul_id' => $request->matkul_id,
        ])->with('success', 'Absensi berhasil ditambahkan!');
    }

    public function edit($id) {
        $absensi = Absensi::findOrFail($id);
        $kelas = Kelas::with('mahasiswa')->findOrFail($absensi->kelas_id);
        $mahasiswa = $kelas->mahasiswa;
        $matkul = MataKuliah::findOrFail($absensi->matkul_id);
        return view('absensi.edit', compact('absensi', 'mahasiswa', 'matkul', 'kelas'));
    }

    public function update(Request $request, $id) {
        $absensi = Absensi::findOrFail($id);
        $absensi->update([
            'tanggal'    => $request->tanggal,
            'status'     => $request->status,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('absensi.index', [
            'kelas_id' => $absensi->kelas_id,
            'matkul_id' => $absensi->matkul_id,
        ])->with('success', 'Absensi berhasil diupdate!');
    }

    public function destroy($id) {
        $absensi = Absensi::findOrFail($id);
        $kelas_id = $absensi->kelas_id;
        $matkul_id = $absensi->matkul_id;
        $absensi->delete();
        return redirect()->route('absensi.index', [
            'kelas_id' => $kelas_id,
            'matkul_id' => $matkul_id,
        ])->with('success', 'Absensi berhasil dihapus!');
    }
}