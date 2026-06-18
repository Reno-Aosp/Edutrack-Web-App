<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\SesiAbsensi;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AbsensiController extends Controller
{
    // =========================================================
    // GET /absensi — Riwayat absensi mahasiswa yang login
    // =========================================================
    public function index(Request $request)
    {
        $user      = Auth::user();
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa) {
            return response()->json(['data' => []]);
        }

        $absensi = Absensi::with(['mataKuliah', 'kelas'])
            ->where('mahasiswa_id', $mahasiswa->id)
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(fn($a) => [
                'id'          => $a->id,
                'matkul_id'   => $a->matkul_id,
                'mata_kuliah' => $a->mataKuliah?->nama ?? '-',
                'kelas'       => $a->kelas?->nama ?? '-',
                'tanggal'     => $a->tanggal,
                'status'      => $a->status,
                'keterangan'  => $a->keterangan ?? '',
                'foto_url'    => $a->foto_url ?? null,
            ]);

        return response()->json(['data' => $absensi]);
    }

    // =========================================================
    // POST /absensi — Input absensi dari Flutter
    // =========================================================
    public function store(Request $request)
    {
        try {
            $request->validate([
                'mahasiswa_id' => 'required|exists:mahasiswa,id',
                'matkul_id'    => 'required|exists:mata_kuliah,id',
                'sesi_id'      => 'required|exists:sesi_absensi,id',
                'status'       => 'required|in:hadir,sakit,izin',
                'keterangan'   => 'nullable|string|max:500',
            ]);

            // Cek sesi masih buka
            $sesi = SesiAbsensi::find($request->sesi_id);
            if (!$sesi || $sesi->status !== 'buka') {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi absensi sudah ditutup.',
                ], 400);
            }

            // Cek sudah absen atau belum
            $sudahAbsen = Absensi::where('mahasiswa_id', $request->mahasiswa_id)
                ->where('matkul_id', $request->matkul_id)
                ->where('tanggal', $sesi->tanggal)
                ->exists();

            if ($sudahAbsen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan absensi untuk mata kuliah ini hari ini.',
                ], 400);
            }

            $absensi = Absensi::create([
                'mahasiswa_id' => $request->mahasiswa_id,
                'matkul_id'    => $request->matkul_id,
                'kelas_id'     => $sesi->kelas_id,
                'tanggal'      => $sesi->tanggal,
                'status'       => $request->status,
                'keterangan'   => $request->keterangan ?? '',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Absensi berhasil dicatat.',
                'data'    => $absensi,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('AbsensiController@store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    // =========================================================
    // POST /absensi/upload-surat — Upload foto surat dari Flutter
    // =========================================================
    // Flutter mengirim: foto_url (sudah diupload ke Supabase Storage),
    // mahasiswa_id, matkul_id, tanggal, status
    // =========================================================
    public function uploadFotoSurat(Request $request)
    {
        try {
            $request->validate([
                'mahasiswa_id' => 'required|exists:mahasiswa,id',
                'matkul_id'    => 'required|exists:mata_kuliah,id',
                'tanggal'      => 'required|date',
                'status'       => 'required|in:sakit,izin',
                'foto_url'     => 'required|string|url',
            ]);

            // Update atau buat record absensi dengan foto_url
            $absensi = Absensi::updateOrCreate(
                [
                    'mahasiswa_id' => $request->mahasiswa_id,
                    'matkul_id'    => $request->matkul_id,
                    'tanggal'      => $request->tanggal,
                ],
                [
                    'status'    => $request->status,
                    'foto_url'  => $request->foto_url,
                ]
            );

            // Ambil nama mahasiswa dan mata kuliah untuk response
            $mahasiswa = Mahasiswa::find($request->mahasiswa_id);
            $matkul    = MataKuliah::find($request->matkul_id);

            return response()->json([
                'success'        => true,
                'message'        => 'Foto surat berhasil disimpan.',
                'foto_url'       => $request->foto_url,
                'nama_mahasiswa' => $mahasiswa?->nama ?? $mahasiswa?->user?->name ?? 'Mahasiswa',
                'mata_kuliah'    => $matkul?->nama ?? '-',
                'data'           => $absensi,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('AbsensiController@uploadFotoSurat error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================
    // DELETE /absensi/{id}
    // =========================================================
    public function destroy($id)
    {
        $absensi = Absensi::findOrFail($id);
        $absensi->delete();

        return response()->json(['success' => true, 'message' => 'Absensi dihapus.']);
    }
}
