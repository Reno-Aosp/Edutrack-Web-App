<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    // =========================================
    // LOGIN
    // =========================================
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
                    ->with('mahasiswa')
                    ->first();

        // cek email & password
        if (!$user || !Hash::check($request->password, $user->password)) {

            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // =========================================
        // HANYA MAHASISWA YANG BOLEH LOGIN FLUTTER
        // =========================================
        if ($user->role !== 'mahasiswa') {

            return response()->json([
                'message' => 'Akses ditolak. Akun ini bukan akun mahasiswa.',
            ], 403);
        }

        // buat token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $user,
        ]);
    }

    // =========================================
    // REGISTER
    // =========================================
    public function register(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|min:6',
            'password_confirmation' => 'required|same:password',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'mahasiswa',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'token'   => $token,
            'user'    => $user,
        ], 201);
    }

    // =========================================
    // UPDATE PROFILE
    // =========================================
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $request->user()->id,
            'prodi' => 'nullable|string|max:100',
        ]);

        $user = $request->user();

        // update tabel users
        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        // update tabel mahasiswa
        if ($request->prodi && $user->mahasiswa) {

            $user->mahasiswa->update([
                'prodi' => $request->prodi,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'user'    => $user->load('mahasiswa'),
        ]);
    }

    // =========================================
    // UPDATE PROFILE PHOTO
    // =========================================
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // max 5MB
        ]);

        $user = $request->user();

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            
            // Store locally in public/profile_photos directory
            $path = $file->storeAs('profile_photos', $filename, 'public');
            
            // Generate full URL
            $url = url('storage/' . $path);

            // Update user record
            $user->update([
                'profile_photo' => $url
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diperbarui',
                'profile_photo' => $url,
                'user'    => $user->load('mahasiswa')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengunggah foto profil'
        ], 400);
    }

    // =========================================
    // UPDATE PHOTO URL (dari Supabase Storage)
    // Menerima URL publik Supabase, simpan ke DB
    // =========================================
    public function updatePhotoUrl(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|url',
        ]);

        $user = $request->user();
        $user->update(['profile_photo' => $request->profile_photo]);

        return response()->json([
            'success'       => true,
            'message'       => 'Foto profil berhasil diperbarui',
            'profile_photo' => $request->profile_photo,
            'user'          => $user->load('mahasiswa'),
        ]);
    }

    // =========================================
    // CHANGE PASSWORD
    // =========================================
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password'      => 'required',
            'new_password'          => 'required|min:6',
            'password_confirmation' => 'required|same:new_password',
        ]);

        $user = $request->user();

        // cek password lama
        if (!Hash::check($request->current_password, $user->password)) {

            return response()->json([
                'message' => 'Password lama tidak sesuai.',
            ], 422);
        }

        // update password baru
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'message' => 'Password berhasil diubah.',
        ]);
    }

    // =========================================
    // PROFILE
    // =========================================
    public function profile(Request $request)
    {
        $user = User::with('mahasiswa')
                    ->find($request->user()->id);

        return response()->json([
            'user' => $user,
        ]);
    }

    // =========================================
    // LOGOUT
    // =========================================
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out',
        ]);
    }
}