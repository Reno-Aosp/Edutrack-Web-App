<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MahasiswaWebController extends Controller
{
    public function index()
    {
        $mahasiswa = Mahasiswa::with(['user', 'kelas'])->get();

        return view('mahasiswa.index', compact('mahasiswa'));
    }

    public function create()
    {
        $kelas = Kelas::all();

        return view('mahasiswa.create', compact('kelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|min:6',
            'nim'        => 'required|unique:mahasiswa,nim',
            'prodi'      => 'required',
            'angkatan'   => 'required',
            'kelas_ids'  => 'nullable|array',
        ]);

        // Create User
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'mahasiswa',
        ]);

        // Create Mahasiswa
        $mahasiswa = Mahasiswa::create([
            'user_id'  => $user->id,
            'nama'     => $request->name,
            'nim'      => $request->nim,
            'prodi'    => $request->prodi,
            'angkatan' => $request->angkatan,
        ]);

        // Sync kelas
        if ($request->filled('kelas_ids')) {
            $mahasiswa->kelas()->sync($request->kelas_ids);
        }

        return redirect()
            ->route('mahasiswa.index')
            ->with('success', 'Mahasiswa berhasil ditambahkan!');
    }

    public function edit($id)
    {
        // Ambil mahasiswa + relasi kelas
        $mahasiswa = Mahasiswa::with('kelas')->findOrFail($id);

        // FIX:
        // hanya tampilkan kelas sesuai angkatan mahasiswa
        $kelas = Kelas::where('angkatan', $mahasiswa->angkatan)->get();

        return view('mahasiswa.edit', compact('mahasiswa', 'kelas'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama'       => 'required|string|max:100',
            'prodi'      => 'required',
            'angkatan'   => 'required',
            'kelas_ids'  => 'nullable|array',
        ]);

        $mahasiswa = Mahasiswa::with('user')->findOrFail($id);

        // Update mahasiswa
        $mahasiswa->update([
            'nama'     => $request->nama,
            'prodi'    => $request->prodi,
            'angkatan' => $request->angkatan,
        ]);

        // Update nama user juga
        if ($mahasiswa->user) {
            $mahasiswa->user->update([
                'name' => $request->nama
            ]);
        }

        // Sync kelas
        if ($request->filled('kelas_ids')) {
            $mahasiswa->kelas()->sync($request->kelas_ids);
        } else {
            $mahasiswa->kelas()->detach();
        }

        return redirect()
            ->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil diupdate!');
    }

    public function destroy($id)
    {
        $mahasiswa = Mahasiswa::with('user')->findOrFail($id);

        // Hapus relasi pivot kelas
        $mahasiswa->kelas()->detach();

        // Hapus user jika ada
        if ($mahasiswa->user) {
            $mahasiswa->user->delete();
        }

        // Hapus mahasiswa
        $mahasiswa->delete();

        return redirect()
            ->route('mahasiswa.index')
            ->with('success', 'Mahasiswa berhasil dihapus!');
    }
}