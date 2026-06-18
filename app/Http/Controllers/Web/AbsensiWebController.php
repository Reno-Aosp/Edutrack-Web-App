<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\SesiAbsensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsensiWebController extends Controller
{
    // =========================================================
    // INDEX — tampilkan absensi per kelas + matkul
    // Admin/dosen bisa melihat foto surat di sini
    // =========================================================
    public function index(Request $request)
    {
        $user = Auth::user();

        // Step 1: Pilih kelas
        if (!$request->kelas_id) {
            if ($user->role === 'dosen') {
                $dosen     = $user->dosen;
                $matkulIds = $dosen
                    ? MataKuliah::where('dosen_id', $dosen->id)->pluck('id')
                    : [];
                $semuaKelas = Kelas::withCount('mahasiswa')
                    ->whereHas('mataKuliah', fn($q) =>
                        $q->whereIn('mata_kuliah.id', $matkulIds))
                    ->get();
            } else {
                $semuaKelas = Kelas::withCount('mahasiswa')->get();
            }
            return view('absensi.index', compact('semuaKelas'));
        }

        // Step 2: Pilih matkul
        $kelas = Kelas::findOrFail($request->kelas_id);
        if (!$request->matkul_id) {
            if ($user->role === 'dosen') {
                $dosen       = $user->dosen;
                $semuaMatkul = $dosen
                    ? $kelas->mataKuliah->where('dosen_id', $dosen->id)->values()
                    : collect();
            } else {
                $semuaMatkul = $kelas->mataKuliah;
            }
            return view('absensi.index', compact('kelas', 'semuaMatkul'));
        }

        // Step 3: Tampil absensi — sudah include foto_url
        $matkul  = MataKuliah::findOrFail($request->matkul_id);
        $absensi = Absensi::with(['mahasiswa'])
            ->where('kelas_id', $request->kelas_id)
            ->where('matkul_id', $request->matkul_id)
            ->orderBy('tanggal', 'desc')
            ->get();

        $sesiAktif = SesiAbsensi::where('status', 'buka')
            ->where('kelas_id', $request->kelas_id)
            ->where('matkul_id', $request->matkul_id)
            ->first();

        return view('absensi.index', compact(
            'kelas', 'matkul', 'absensi', 'sesiAktif'
        ));
    }

    // =========================================================
    // STORE — input manual dari web
    // =========================================================
    public function store(Request $request)
    {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswa,id',
            'matkul_id'    => 'required|exists:mata_kuliah,id',
            'kelas_id'     => 'required|exists:kelas,id',
            'tanggal'      => 'required|date',
            'status'       => 'required|in:hadir,sakit,izin,alpha',
        ]);

        Absensi::updateOrCreate(
            [
                'mahasiswa_id' => $request->mahasiswa_id,
                'matkul_id'    => $request->matkul_id,
                'kelas_id'     => $request->kelas_id,
                'tanggal'      => $request->tanggal,
            ],
            [
                'status'     => $request->status,
                'keterangan' => $request->keterangan,
            ]
        );

        return back()->with('success', 'Absensi berhasil disimpan!');
    }

    public function edit($id)
    {
        $absensi   = Absensi::with(['mahasiswa', 'mataKuliah', 'kelas'])->findOrFail($id);
        $kelas     = $absensi->kelas;
        $matkul    = $absensi->mataKuliah;
        $mahasiswa = $kelas ? $kelas->mahasiswa : collect();
        return view('absensi.edit', compact('absensi', 'kelas', 'matkul', 'mahasiswa'));
    }

    public function update(Request $request, $id)
    {
        $absensi = Absensi::findOrFail($id);
        $absensi->update([
            'status'     => $request->status,
            'keterangan' => $request->keterangan,
        ]);
        return back()->with('success', 'Absensi berhasil diupdate!');
    }

    public function destroy($id)
    {
        Absensi::findOrFail($id)->delete();
        return back()->with('success', 'Absensi berhasil dihapus!');
    }
}
