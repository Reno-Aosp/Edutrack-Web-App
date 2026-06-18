<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SesiAbsensi;
use App\Models\MataKuliah;
use App\Models\Kelas;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SesiAbsensiController extends Controller
{
    private function autoTutupSesiKadaluarsa()
    {
        try {
            $sekarang = Carbon::now();
            $hari_ini = Carbon::today()->toDateString();

            $sesiList = SesiAbsensi::where('status', 'buka')
                ->where('tanggal', $hari_ini)
                ->whereNotNull('jam_tutup')
                ->get();

            foreach ($sesiList as $sesi) {
                $jamTutup = trim($sesi->jam_tutup);
                if (empty($jamTutup) || $jamTutup == '00:00:00' || $jamTutup == '0') continue;
                try {
                    $format     = strlen($jamTutup) == 5 ? 'H:i' : 'H:i:s';
                    $waktuTutup = Carbon::createFromFormat($format, $jamTutup);
                    if ($sekarang->gte($waktuTutup)) {
                        $sesi->update(['status' => 'tutup']);
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            SesiAbsensi::where('status', 'buka')
                ->where('tanggal', '<', $hari_ini)
                ->update(['status' => 'tutup']);

        } catch (\Exception $e) {
            Log::error('autoTutupSesiKadaluarsa error: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // WEB : HALAMAN INDEX — filter per dosen
    // ─────────────────────────────────────────────────────────────
    public function index()
    {
        $this->autoTutupSesiKadaluarsa();

        $user  = Auth::user();
        $dosen = $user->dosen ?? null;

        if ($user->role === 'dosen' && $dosen) {
            // Matkul yang diajar dosen ini
            $matkulIds = MataKuliah::where('dosen_id', $dosen->id)->pluck('id');

            // Kelas yang terkait matkul dosen ini
            $kelasIds = Kelas::whereHas('mataKuliah', function ($q) use ($matkulIds) {
                $q->whereIn('mata_kuliah.id', $matkulIds);
            })->pluck('id');

            $matkul = MataKuliah::whereIn('id', $matkulIds)->get();
            $kelas  = Kelas::whereIn('id', $kelasIds)->get();

            // Riwayat sesi hanya matkul yang dia ajar
            $sesi = SesiAbsensi::with(['mataKuliah', 'kelas'])
                ->whereIn('matkul_id', $matkulIds)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

        } else {
            // Admin: lihat semua
            $matkul = MataKuliah::all();
            $kelas  = Kelas::all();
            $sesi   = SesiAbsensi::with(['mataKuliah', 'kelas'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        return view('sesi_absensi.index', compact('matkul', 'kelas', 'sesi'));
    }

    // ─────────────────────────────────────────────────────────────
    // WEB : BUKA SESI — validasi dosen hanya bisa buka matkul miliknya
    // ─────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        try {
            Log::info('STORE SESI REQUEST', $request->all());

            $request->validate([
                'matkul_id'    => 'required|exists:mata_kuliah,id',
                'kelas_id'     => 'required|exists:kelas,id',
                'jam_buka'     => 'required',
                'jam_tutup'    => 'required',
                'pertemuan_ke' => 'nullable|integer|min:1',
            ]);

            // Validasi dosen hanya bisa buka matkul miliknya
            $user  = Auth::user();
            $dosen = $user->dosen ?? null;
            if ($user->role === 'dosen' && $dosen) {
                $matkul = MataKuliah::find($request->matkul_id);
                if (!$matkul || $matkul->dosen_id != $dosen->id) {
                    return back()->with('error', 'Anda tidak berhak membuka sesi untuk mata kuliah ini!');
                }
            }

            $jamTutup = strlen($request->jam_tutup) == 5
                ? $request->jam_tutup . ':00'
                : $request->jam_tutup;

            $cek = SesiAbsensi::where('status', 'buka')
                ->where('kelas_id', $request->kelas_id)
                ->where('matkul_id', $request->matkul_id)
                ->where('tanggal', Carbon::today()->toDateString())
                ->first();

            if ($cek) {
                return back()->with('error', 'Masih ada sesi aktif untuk kelas dan mata kuliah ini!');
            }

            SesiAbsensi::create([
                'matkul_id'    => $request->matkul_id,
                'kelas_id'     => $request->kelas_id,
                'tanggal'      => Carbon::today()->toDateString(),
                'jam_buka'     => Carbon::now()->format('H:i:s'),
                'jam_tutup'    => $jamTutup,
                'pertemuan_ke' => $request->pertemuan_ke,
                'status'       => 'buka',
            ]);

            return back()->with('success', 'Sesi absensi berhasil dibuka!');

        } catch (\Exception $e) {
            Log::error('STORE SESI ERROR : ' . $e->getMessage());
            return back()->with('error', 'Gagal membuka sesi absensi!');
        }
    }

    public function tutup($id)
    {
        try {
            $sesi  = SesiAbsensi::findOrFail($id);
            $user  = Auth::user();
            $dosen = $user->dosen ?? null;

            // Validasi dosen hanya bisa tutup sesi matkulnya sendiri
            if ($user->role === 'dosen' && $dosen) {
                $matkul = MataKuliah::find($sesi->matkul_id);
                if (!$matkul || $matkul->dosen_id != $dosen->id) {
                    return back()->with('error', 'Anda tidak berhak menutup sesi ini!');
                }
            }

            $sesi->update(['status' => 'tutup']);
            return back()->with('success', 'Sesi absensi berhasil ditutup!');

        } catch (\Exception $e) {
            Log::error('TUTUP SESI ERROR : ' . $e->getMessage());
            return back()->with('error', 'Gagal menutup sesi!');
        }
    }

    public function destroy($id)
    {
        try {
            $sesi  = SesiAbsensi::findOrFail($id);
            $user  = Auth::user();
            $dosen = $user->dosen ?? null;

            if ($user->role === 'dosen' && $dosen) {
                $matkul = MataKuliah::find($sesi->matkul_id);
                if (!$matkul || $matkul->dosen_id != $dosen->id) {
                    return back()->with('error', 'Anda tidak berhak menghapus sesi ini!');
                }
            }

            $sesi->delete();
            return back()->with('success', 'Sesi absensi berhasil dihapus!');

        } catch (\Exception $e) {
            Log::error('HAPUS SESI ERROR : ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus sesi!');
        }
    }

    // ─────────────────────────────────────────────────────────────
    // API : SESI AKTIF (untuk Flutter mahasiswa)
    // ─────────────────────────────────────────────────────────────
    public function aktif(Request $request)
    {
        $this->autoTutupSesiKadaluarsa();

        $user      = $request->user();
        $mahasiswa = $user->mahasiswa;

        $kelasIds = $mahasiswa
            ? $mahasiswa->kelas->pluck('id')
            : [];

        $sesi = SesiAbsensi::where('status', 'buka')
            ->whereIn('kelas_id', $kelasIds)
            ->with(['mataKuliah', 'kelas'])
            ->get();

        return response()->json([
            'data' => $sesi->map(fn($s) => [
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
            ]),
        ]);
    }

    public function riwayat()
    {
        $this->autoTutupSesiKadaluarsa();

        $sesi = SesiAbsensi::with(['mataKuliah', 'kelas'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $sesi->map(fn($s) => [
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
            ]),
        ]);
    }
}
