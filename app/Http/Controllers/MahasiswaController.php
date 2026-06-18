<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MahasiswaController extends Controller {

    public function index() {
        $mahasiswa = Mahasiswa::with('user', 'kelas')->get();
        return view('mahasiswa.index', compact('mahasiswa'));
    }

    public function create() {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        return view('mahasiswa.create', compact('kelas'));
    }

    public function store(Request $request) {
        $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'nim'       => 'required|string|max:20|unique:mahasiswa',
            'prodi'     => 'required|string|max:50',
            'angkatan'  => 'required|string|max:10',
            'kelas_ids' => 'nullable|array',
            'kelas_ids.*' => 'exists:kelas,id',
        ]);

        // 1. Buat user dulu
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'mahasiswa',
        ]);

        // 2. Buat mahasiswa linked ke user
        $mahasiswa = Mahasiswa::create([
            'user_id'  => $user->id,
            'nim'      => $request->nim,
            'prodi'    => $request->prodi,
            'angkatan' => $request->angkatan,
        ]);

        // 3. Assign kelas yang dipilih
        if ($request->filled('kelas_ids')) {
            $mahasiswa->kelas()->attach($request->kelas_ids);
        } else {
            // Auto-assign berdasarkan prodi + angkatan kalau tidak dipilih
            $this->autoAssignKelas($mahasiswa);
        }

        return redirect()->route('mahasiswa.index')
            ->with('success', 'Mahasiswa berhasil ditambahkan!');
    }

    public function show($id) {
        $mahasiswa = Mahasiswa::with('user', 'nilai', 'absensi', 'kelas')
                        ->findOrFail($id);
        return response()->json($mahasiswa);
    }

    public function edit($id) {
        $mahasiswa = Mahasiswa::with('user', 'kelas')->findOrFail($id);
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $selectedKelas = $mahasiswa->kelas->pluck('id')->toArray();
        return view('mahasiswa.edit', compact('mahasiswa', 'kelas', 'selectedKelas'));
    }

    public function update(Request $request, $id) {
        $mahasiswa = Mahasiswa::with('user')->findOrFail($id);

        $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email,' . $mahasiswa->user_id,
            'nim'       => 'required|string|max:20|unique:mahasiswa,nim,' . $id,
            'prodi'     => 'required|string|max:50',
            'angkatan'  => 'required|string|max:10',
            'kelas_ids' => 'nullable|array',
            'kelas_ids.*' => 'exists:kelas,id',
        ]);

        // Update user
        $mahasiswa->user->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        // Update password kalau diisi
        if ($request->filled('password')) {
            $mahasiswa->user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        // Update mahasiswa
        $mahasiswa->update([
            'nim'      => $request->nim,
            'prodi'    => $request->prodi,
            'angkatan' => $request->angkatan,
        ]);

        // Sync kelas (hapus lama, assign baru)
        if ($request->filled('kelas_ids')) {
            $mahasiswa->kelas()->sync($request->kelas_ids);
        } else {
            $mahasiswa->kelas()->detach();
            $this->autoAssignKelas($mahasiswa->fresh());
        }

        return redirect()->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil diupdate!');
    }

    public function destroy($id) {
        $mahasiswa = Mahasiswa::findOrFail($id);
        $userId = $mahasiswa->user_id;
        $mahasiswa->kelas()->detach();
        $mahasiswa->delete();
        User::destroy($userId); // hapus user juga
        return redirect()->route('mahasiswa.index')
            ->with('success', 'Mahasiswa berhasil dihapus!');
    }

    // ── Auto-assign kelas berdasarkan prodi + angkatan ────────────────────
    private function autoAssignKelas(Mahasiswa $mahasiswa) {
        $kelas = Kelas::where('prodi', $mahasiswa->prodi)
                      ->where('angkatan', $mahasiswa->angkatan)
                      ->first();

        if (!$kelas) {
            $kelas = Kelas::where('prodi', $mahasiswa->prodi)->first();
        }

        if ($kelas) {
            $mahasiswa->kelas()->attach($kelas->id);
        }
    }
}