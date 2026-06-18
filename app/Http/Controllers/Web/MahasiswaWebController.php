<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MahasiswaWebController extends Controller {

    public function index() {
        $mahasiswa = Mahasiswa::with(['user', 'kelas'])->get();
        return view('mahasiswa.index', compact('mahasiswa'));
    }

    public function create() {
        $kelas = Kelas::all();
        return view('mahasiswa.create', compact('kelas'));
    }

    public function store(Request $request) {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6',
            'nim'      => 'required|unique:mahasiswa',
            'prodi'    => 'required',
            'angkatan' => 'required',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'mahasiswa',
        ]);

        $mahasiswa = Mahasiswa::create([
            'user_id'  => $user->id,
            'nama'     => $request->name,
            'nim'      => $request->nim,
            'prodi'    => $request->prodi,
            'angkatan' => $request->angkatan,
        ]);

        if ($request->kelas_ids) {
            $mahasiswa->kelas()->sync($request->kelas_ids);
        }

        return redirect()->route('mahasiswa.index')
            ->with('success', 'Mahasiswa berhasil ditambahkan!');
    }

    public function edit($id) {
        $mahasiswa = Mahasiswa::with('kelas')->findOrFail($id);
        $kelas = Kelas::all();
        return view('mahasiswa.edit', compact('mahasiswa', 'kelas'));
    }

    public function update(Request $request, $id) {
        $mahasiswa = Mahasiswa::findOrFail($id);
        $mahasiswa->update([
            'nama'     => $request->nama,
            'prodi'    => $request->prodi,
            'angkatan' => $request->angkatan,
        ]);

        if ($mahasiswa->user) {
            $mahasiswa->user->update(['name' => $request->nama]);
        }

        if ($request->kelas_ids) {
            $mahasiswa->kelas()->sync($request->kelas_ids);
        } else {
            $mahasiswa->kelas()->detach();
        }

        return redirect()->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil diupdate!');
    }

    public function destroy($id) {
        $mahasiswa = Mahasiswa::findOrFail($id);
        if ($mahasiswa->user) {
            $mahasiswa->user->delete();
        }
        $mahasiswa->delete();
        return redirect()->route('mahasiswa.index')
            ->with('success', 'Mahasiswa berhasil dihapus!');
    }
}