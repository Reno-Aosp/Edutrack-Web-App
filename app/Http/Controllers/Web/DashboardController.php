<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Dosen;
use App\Models\Absensi;
use App\Models\Nilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller {

    public function index() {
        $totalMahasiswa = Mahasiswa::count();
        $totalMataKuliah = MataKuliah::count();
        $totalDosen = Dosen::count();
        $totalKelas = Kelas::count();
        $totalAbsensi = Absensi::count();
        $totalNilai = Nilai::count();
        $recentMahasiswa = Mahasiswa::latest()->take(5)->get();
        
        return view('dashboard', compact(
            'totalMahasiswa', 
            'totalMataKuliah',
            'totalDosen',
            'totalKelas',
            'totalAbsensi',
            'totalNilai',
            'recentMahasiswa'
        ));
    }

    public function login(Request $request) {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();

            if ($user->role === 'mahasiswa') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akses ditolak! Mahasiswa tidak dapat mengakses halaman ini.',
                ]);
            }

            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ]);
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function createUser() {
        $kelas = Kelas::all();
        return view('users.create', compact('kelas'));
    }

    public function storeUser(Request $request) {
        // Get selected kelas and extract angkatan/semester from first kelas
        $angkatanForDB = null;
        if ($request->kelas_ids && count($request->kelas_ids) > 0) {
            $firstKelas = Kelas::find($request->kelas_ids[0]);
            if ($firstKelas) {
                $angkatanForDB = $firstKelas->angkatan;
            }
        }

        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role'     => 'required|in:admin,dosen,mahasiswa',
            'nim'      => 'required_if:role,mahasiswa|unique:mahasiswa,nim',
            'prodi'    => 'required_if:role,mahasiswa',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        // ✅ Auto buat dosen jika role dosen
        if ($request->role === 'dosen') {
            $dosenExist = \App\Models\Dosen::where('user_id', $user->id)->first();
            if (!$dosenExist) {
                \App\Models\Dosen::create([
                    'user_id' => $user->id,
                    'nidn'    => 'NIDN-' . $user->id,
                ]);
            }
        }

        // ✅ Auto buat mahasiswa jika role mahasiswa
        if ($request->role === 'mahasiswa') {
            $mahasiswaExist = Mahasiswa::where('user_id', $user->id)->first();
            if (!$mahasiswaExist) {
                $mahasiswa = Mahasiswa::create([
                    'user_id'  => $user->id,
                    'nama'     => $request->name,
                    'nim'      => $request->nim,
                    'prodi'    => $request->prodi,
                    'angkatan' => $angkatanForDB,
                ]);

                // Sync dengan kelas jika ada pilihan
                if ($request->kelas_ids) {
                    $mahasiswa->kelas()->sync($request->kelas_ids);
                }
            }
        }

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan!');
    }

    public function userList() {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    public function editUser($id) {
        $user = User::findOrFail($id);
        $kelas = Kelas::all();
        return view('users.edit', compact('user', 'kelas'));
    }

    public function updateUser(Request $request, $id) {
        // Get selected kelas and extract angkatan/semester from first kelas
        $angkatanForDB = null;
        if ($request->kelas_ids && count($request->kelas_ids) > 0) {
            $firstKelas = Kelas::find($request->kelas_ids[0]);
            if ($firstKelas) {
                $angkatanForDB = $firstKelas->angkatan;
            }
        }

        $user = User::findOrFail($id);
        $mahasiswa = Mahasiswa::where('user_id', $user->id)->first();

        // Add validation for NIM
        $request->validate([
            'nim'      => 'required_if:role,mahasiswa|unique:mahasiswa,nim' . ($mahasiswa ? ',' . $mahasiswa->id : ''),
            'prodi'    => 'required_if:role,mahasiswa',
        ]);

        $user->name = $request->name;
        $user->role = $request->role;

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // ✅ Cek by user_id, bukan hanya relasi
        if ($request->role === 'dosen') {
            $dosenExist = \App\Models\Dosen::where('user_id', $user->id)->first();
            if (!$dosenExist) {
                \App\Models\Dosen::create([
                    'user_id' => $user->id,
                    'nidn'    => 'NIDN-' . $user->id,
                ]);
            }
        }

        // ✅ Handle mahasiswa
        if ($request->role === 'mahasiswa') {
            if (!$mahasiswa) {
                $mahasiswa = Mahasiswa::create([
                    'user_id'  => $user->id,
                    'nama'     => $user->name,
                    'nim'      => $request->nim,
                    'prodi'    => $request->prodi,
                    'angkatan' => $angkatanForDB,
                ]);
            } else {
                $mahasiswa->update([
                    'nama'     => $user->name,
                    'nim'      => $request->nim,
                    'prodi'    => $request->prodi,
                    'angkatan' => $angkatanForDB,
                ]);
            }

            if ($request->kelas_ids) {
                $mahasiswa->kelas()->sync($request->kelas_ids);
            } else {
                $mahasiswa->kelas()->detach();
            }
        }

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diupdate!');
    }

    public function destroyUser($id) {
        User::findOrFail($id)->delete();
        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus!');
    }
}