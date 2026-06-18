<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DosenWebController extends Controller {

    public function index() {
        $dosen = Dosen::with('user')->get();
        return view('dosen.index', compact('dosen'));
    }

    public function create() {
        return view('dosen.create');
    }

    public function store(Request $request) {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6',
            'nidn'     => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'dosen',
        ]);

        Dosen::create([
            'user_id' => $user->id,
            'nidn'    => $request->nidn,
        ]);

        return redirect()->route('dosen.index')
            ->with('success', 'Dosen berhasil ditambahkan!');
    }

    public function edit($id) {
        $dosen = Dosen::with('user')->findOrFail($id);
        return view('dosen.edit', compact('dosen'));
    }

    public function update(Request $request, $id) {
        $dosen = Dosen::findOrFail($id);

        $dosen->update(['nidn' => $request->nidn]);

        if ($dosen->user) {
            $dosen->user->update(['name' => $request->name]);
            if ($request->password) {
                $dosen->user->update(['password' => Hash::make($request->password)]);
            }
        }

        return redirect()->route('dosen.index')
            ->with('success', 'Data dosen berhasil diupdate!');
    }

    public function destroy($id) {
        $dosen = Dosen::findOrFail($id);
        if ($dosen->user) {
            $dosen->user->delete();
        }
        $dosen->delete();
        return redirect()->route('dosen.index')
            ->with('success', 'Dosen berhasil dihapus!');
    }
}