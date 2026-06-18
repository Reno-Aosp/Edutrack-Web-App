<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller {

    public function index() {
        $totalMahasiswa = Mahasiswa::count();
        $totalMataKuliah = MataKuliah::count();
        return view('dashboard', compact('totalMahasiswa', 'totalMataKuliah'));
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
        return view('users.create');
    }

    public function storeUser(Request $request) {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role'     => 'required|in:admin,dosen,mahasiswa',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan!');
    }

    public function userList() {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    public function editUser($id) {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    public function updateUser(Request $request, $id) {
        $user = User::findOrFail($id);

        $user->name = $request->name;
        $user->role = $request->role;

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diupdate!');
    }

    public function destroyUser($id) {
        User::findOrFail($id)->delete();
        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus!');
    }
}