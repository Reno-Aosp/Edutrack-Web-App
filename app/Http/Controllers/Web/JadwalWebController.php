<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalWebController extends Controller
{
    public function index(Request $request)
    {
        $user    = Auth::user();
        $isDosen = $user->role === 'dosen';
        $dosen   = $user->dosen ?? null;

        // ── Dosen hanya lihat kelas yang dia ajar ──
        if ($isDosen && $dosen) {
            $matkulIds = MataKuliah::where('dosen_id', $dosen->id)->pluck('id');
            $semuaKelas = Kelas::whereHas('mataKuliah', fn($q) =>
                $q->whereIn('mata_kuliah.id', $matkulIds)
            )->get();
        } else {
            $semuaKelas = Kelas::all();
        }

        if ($request->kelas_id) {
            $kelas = Kelas::findOrFail($request->kelas_id);

            // Jadwal filter per dosen jika role dosen
            $query = Jadwal::with(['mataKuliah', 'dosen.user'])
                ->where('kelas_id', $request->kelas_id);

            if ($isDosen && $dosen) {
                $matkulIds = MataKuliah::where('dosen_id', $dosen->id)->pluck('id');
                $query->whereIn('matkul_id', $matkulIds);
            }

            $jadwal = $query
                ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')")
                ->orderBy('jam_mulai')
                ->get();

            return view('jadwal.index', compact('kelas', 'jadwal', 'semuaKelas', 'isDosen'));
        }

        return view('jadwal.index', compact('semuaKelas', 'isDosen'));
    }

    public function create(Request $request)
    {
        $user     = Auth::user();
        $isDosen  = $user->role === 'dosen';
        $dosen_db = $user->dosen ?? null;

        $kelas_id = $request->kelas_id;
        $kelas    = Kelas::findOrFail($kelas_id);

        // Dosen hanya lihat matkul yang dia ajar di kelas ini
        if ($isDosen && $dosen_db) {
            $matkulIds = MataKuliah::where('dosen_id', $dosen_db->id)->pluck('id');
            $matkul    = $kelas->mataKuliah->whereIn('id', $matkulIds->toArray())->values();
        } else {
            $matkul = $kelas->mataKuliah;
        }

        $dosen = Dosen::with('user')->get();
        return view('jadwal.create', compact('kelas', 'matkul', 'dosen', 'isDosen', 'dosen_db'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kelas_id'    => 'required|exists:kelas,id',
            'matkul_id'   => 'required|exists:mata_kuliah,id',
            'dosen_id'    => 'nullable|exists:dosen,id',
            'hari'        => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required',
            'ruangan'     => 'nullable|string|max:50',
        ]);

        // Dosen hanya bisa tambah jadwal matkulnya sendiri
        $user  = Auth::user();
        $dosen = $user->dosen ?? null;
        if ($user->role === 'dosen' && $dosen) {
            $matkul = MataKuliah::find($request->matkul_id);
            if (!$matkul || $matkul->dosen_id != $dosen->id) {
                return back()->with('error', 'Anda tidak berhak menambah jadwal untuk mata kuliah ini!');
            }
            // Auto-set dosen_id ke dosen yang login
            $request->merge(['dosen_id' => $dosen->id]);
        }

        Jadwal::create($request->all());

        return redirect()
            ->route('jadwal.index', ['kelas_id' => $request->kelas_id])
            ->with('success', 'Jadwal berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $user     = Auth::user();
        $isDosen  = $user->role === 'dosen';
        $dosen_db = $user->dosen ?? null;

        $jadwal = Jadwal::findOrFail($id);
        $kelas  = Kelas::findOrFail($jadwal->kelas_id);

        if ($isDosen && $dosen_db) {
            $matkulIds = MataKuliah::where('dosen_id', $dosen_db->id)->pluck('id');
            $matkul    = $kelas->mataKuliah->whereIn('id', $matkulIds->toArray())->values();
        } else {
            $matkul = $kelas->mataKuliah;
        }

        $dosen = Dosen::with('user')->get();
        return view('jadwal.edit', compact('jadwal', 'kelas', 'matkul', 'dosen', 'isDosen', 'dosen_db'));
    }

    public function update(Request $request, $id)
    {
        $jadwal = Jadwal::findOrFail($id);

        // Validasi dosen
        $user  = Auth::user();
        $dosen = $user->dosen ?? null;
        if ($user->role === 'dosen' && $dosen) {
            $matkul = MataKuliah::find($request->matkul_id ?? $jadwal->matkul_id);
            if (!$matkul || $matkul->dosen_id != $dosen->id) {
                return back()->with('error', 'Anda tidak berhak mengubah jadwal ini!');
            }
        }

        $jadwal->update($request->all());
        return redirect()
            ->route('jadwal.index', ['kelas_id' => $jadwal->kelas_id])
            ->with('success', 'Jadwal berhasil diupdate!');
    }

    public function destroy($id)
    {
        $jadwal   = Jadwal::findOrFail($id);
        $kelas_id = $jadwal->kelas_id;

        $user  = Auth::user();
        $dosen = $user->dosen ?? null;
        if ($user->role === 'dosen' && $dosen) {
            $matkul = MataKuliah::find($jadwal->matkul_id);
            if (!$matkul || $matkul->dosen_id != $dosen->id) {
                return back()->with('error', 'Anda tidak berhak menghapus jadwal ini!');
            }
        }

        $jadwal->delete();
        return redirect()
            ->route('jadwal.index', ['kelas_id' => $kelas_id])
            ->with('success', 'Jadwal berhasil dihapus!');
    }
}
