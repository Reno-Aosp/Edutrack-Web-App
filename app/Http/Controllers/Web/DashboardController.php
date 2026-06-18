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

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ── DASHBOARD DOSEN ──────────────────────────────────────
        if ($user->role === 'dosen') {
            $dosen = $user->dosen;

            // Matkul yang dia ajar (dengan relasi kelas)
            $dosenMatkul = $dosen
                ? MataKuliah::with('kelas')
                    ->where('dosen_id', $dosen->id)
                    ->get()
                : collect();

            // Kelas unik dari semua matkul yang dia ajar
            $dosenKelas = $dosenMatkul
                ->flatMap(fn($mk) => $mk->kelas)
                ->unique('id')
                ->values();

            // Hitung total mahasiswa unik di semua kelas yang diajar
            $kelasIds = $dosenKelas->pluck('id');
            $dosenTotalMahasiswa = Mahasiswa::whereHas('kelas', function ($q) use ($kelasIds) {
                $q->whereIn('kelas.id', $kelasIds);
            })->count();

            return view('dashboard', compact(
                'dosenMatkul',
                'dosenKelas',
                'dosenTotalMahasiswa'
            ));
        }

        // ── DASHBOARD ADMIN ──────────────────────────────────────
        $totalMahasiswa  = Mahasiswa::count();
        $totalMataKuliah = MataKuliah::count();
        $totalDosen      = Dosen::count();
        $totalKelas      = Kelas::count();
        $totalAbsensi    = Absensi::count();
        $totalNilai      = Nilai::count();
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

    // ─────────────────────────────────────────────────────────────
    // AUTH
    // ─────────────────────────────────────────────────────────────
    public function login(Request $request)
    {
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

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // ─────────────────────────────────────────────────────────────
    // USER MANAGEMENT
    // ─────────────────────────────────────────────────────────────
    public function userList()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    public function createUser()
    {
        $kelas = Kelas::all();
        return view('users.create', compact('kelas'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role'     => 'required|in:admin,dosen,mahasiswa',
            'nim'      => 'required_if:role,mahasiswa|nullable|unique:mahasiswa,nim',
            'prodi'    => 'required_if:role,mahasiswa|nullable',
            'kelas_id' => 'nullable|exists:kelas,id',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        if ($request->role === 'dosen') {
            \App\Models\Dosen::firstOrCreate(
                ['user_id' => $user->id],
                ['nidn'    => 'NIDN-' . $user->id]
            );
        }

        if ($request->role === 'mahasiswa') {
            $angkatanForDB = null;
            if ($request->kelas_id) {
                $angkatanForDB = Kelas::find($request->kelas_id)?->angkatan;
            }
            $mahasiswa = Mahasiswa::create([
                'user_id'  => $user->id,
                'nama'     => $request->name,
                'nim'      => $request->nim,
                'prodi'    => $request->prodi,
                'angkatan' => $angkatanForDB,
            ]);
            if ($request->kelas_id) {
                $mahasiswa->kelas()->sync([$request->kelas_id]);
            }
        }

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan!');
    }

    public function editUser($id)
    {
        $user  = User::with(['mahasiswa.kelas'])->findOrFail($id);
        $kelas = Kelas::all();
        return view('users.edit', compact('user', 'kelas'));
    }

    public function updateUser(Request $request, $id)
    {
        $user      = User::findOrFail($id);
        $mahasiswa = Mahasiswa::where('user_id', $user->id)->first();

        $request->validate([
            'name'     => 'required|string|max:100',
            'role'     => 'required|in:admin,dosen,mahasiswa',
            'nim'      => 'required_if:role,mahasiswa|nullable|unique:mahasiswa,nim'
                          . ($mahasiswa ? ',' . $mahasiswa->id : ''),
            'prodi'    => 'required_if:role,mahasiswa|nullable',
            'kelas_id' => 'nullable|exists:kelas,id',
            'password' => 'nullable|min:6',
        ]);

        $user->name = $request->name;
        $user->role = $request->role;

        if ($request->filled('password') && $request->role === 'admin') {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        if ($request->role === 'dosen') {
            \App\Models\Dosen::firstOrCreate(
                ['user_id' => $user->id],
                ['nidn'    => 'NIDN-' . $user->id]
            );
        }

        if ($request->role === 'mahasiswa') {
            $angkatanForDB = null;
            if ($request->kelas_id) {
                $angkatanForDB = Kelas::find($request->kelas_id)?->angkatan;
            }
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
            if ($request->kelas_id) {
                $mahasiswa->kelas()->sync([$request->kelas_id]);
            } else {
                $mahasiswa->kelas()->detach();
            }
        }

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diupdate!');
    }

    public function destroyUser($id)
    {
        User::findOrFail($id)->delete();
        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus!');
    }
}
