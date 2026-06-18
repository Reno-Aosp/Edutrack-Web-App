<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SesiAbsensi;
use App\Models\MataKuliah;
use App\Models\Kelas;
use Carbon\Carbon;

class SesiAbsensiController extends Controller
{
    // ── Auto tutup sesi yang sudah lewat jam tutup ──────────────────────────
    private function autoTutupSesiKadaluarsa()
    {
        $sekarang = Carbon::now()->format('H:i');
        $hari_ini = Carbon::today()->toDateString();

        SesiAbsensi::where('status', 'buka')
            ->whereNotNull('jam_tutup')
            ->where('tanggal', $hari_ini)
            ->where('jam_tutup', '<=', $sekarang)
            ->update(['status' => 'tutup']);
    }

    // ── WEB: halaman index ──────────────────────────────────────────────────
    public function index()
    {
        $this->autoTutupSesiKadaluarsa();

        $matkul = MataKuliah::all();
        $kelas  = Kelas::all();
        $sesi   = SesiAbsensi::with(['mataKuliah', 'kelas'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);

        return view('sesi_absensi.index', compact('matkul', 'kelas', 'sesi'));
    }

    // ── WEB: buka sesi (FIX WAJIB jam_tutup) ────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'matkul_id'    => 'required|exists:mata_kuliah,id',
            'kelas_id'     => 'required|exists:kelas,id',
            'jam_buka'     => 'required',
            'jam_tutup'    => 'required|after:jam_buka', // ✅ wajib + harus setelah jam buka
            'pertemuan_ke' => 'nullable|integer|min:1',
        ]);

        SesiAbsensi::create([
            'matkul_id'    => $request->matkul_id,
            'kelas_id'     => $request->kelas_id,
            'tanggal'      => Carbon::today()->toDateString(),

            // pakai input dari form (bukan auto Carbon lagi)
            'jam_buka'     => $request->jam_buka,
            'jam_tutup'    => $request->jam_tutup,

            'pertemuan_ke' => $request->pertemuan_ke,
            'status'       => 'buka',
        ]);

        return back()->with('success', 'Sesi absensi berhasil dibuka!');
    }

    // ── WEB: tutup sesi manual ─────────────────────────────────────────────
    public function tutup($id)
    {
        $sesi = SesiAbsensi::findOrFail($id);

        $sesi->update([
            'status'    => 'tutup',
            'jam_tutup' => Carbon::now()->format('H:i'), // overwrite saat ditutup manual
        ]);

        return back()->with('success', 'Sesi absensi berhasil ditutup!');
    }

    // ── WEB: hapus sesi ────────────────────────────────────────────────────
    public function destroy($id)
    {
        SesiAbsensi::findOrFail($id)->delete();

        return back()->with('success', 'Sesi absensi berhasil dihapus!');
    }

    // ── API: sesi aktif ────────────────────────────────────────────────────
    public function aktif(Request $request)
    {
        $this->autoTutupSesiKadaluarsa();

        $user      = $request->user();
        $mahasiswa = $user->mahasiswa;

        $kelasIds = $mahasiswa ? $mahasiswa->kelas->pluck('id') : [];

        $sesi = SesiAbsensi::where('status', 'buka')
            ->whereIn('kelas_id', $kelasIds)
            ->with(['mataKuliah', 'kelas'])
            ->get();

        return response()->json([
            'data' => $sesi->map(function ($s) {
                return [
                    'id'           => $s->id,
                    'matkul_id'    => $s->matkul_id,
                    'matkul_nama'  => $s->mataKuliah->nama ?? '-',
                    'kelas_id'     => $s->kelas_id,
                    'kelas_nama'   => $s->kelas->nama_kelas ?? '-',
                    'tanggal'      => $s->tanggal,
                    'jam_buka'     => $s->jam_buka,
                    'jam_tutup'    => $s->jam_tutup,
                    'pertemuan_ke' => $s->pertemuan_ke,
                    'status'       => $s->status,
                ];
            }),
        ]);
    }

    // ── API: riwayat ───────────────────────────────────────────────────────
    public function riwayat()
    {
        $sesi = SesiAbsensi::with(['mataKuliah', 'kelas'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $sesi->map(function ($s) {
                return [
                    'id'           => $s->id,
                    'matkul_id'    => $s->matkul_id,
                    'matkul_nama'  => $s->mataKuliah->nama ?? '-',
                    'kelas_id'     => $s->kelas_id,
                    'kelas_nama'   => $s->kelas->nama_kelas ?? '-',
                    'tanggal'      => $s->tanggal,
                    'jam_buka'     => $s->jam_buka,
                    'jam_tutup'    => $s->jam_tutup,
                    'pertemuan_ke' => $s->pertemuan_ke,
                    'status'       => $s->status,
                ];
            }),
        ]);
    }
}