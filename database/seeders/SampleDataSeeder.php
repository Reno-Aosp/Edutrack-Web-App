<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // Data Mata Kuliah (diperluas dengan lebih banyak mata kuliah)
        $mataKuliah = [
            // Teknik Informatika
            ['nama' => 'Pemrograman Perangkat Bergerak', 'kode' => 'TI301', 'sks' => 3, 'dosen_id' => 1],
            ['nama' => 'Kecerdasan Buatan', 'kode' => 'TI312', 'sks' => 3, 'dosen_id' => 1],
            ['nama' => 'Pemrograman Web', 'kode' => 'TI302', 'sks' => 3, 'dosen_id' => 1],
            ['nama' => 'Basis Data', 'kode' => 'TI310', 'sks' => 3, 'dosen_id' => 3],
            ['nama' => 'Jaringan Komputer', 'kode' => 'TI315', 'sks' => 3, 'dosen_id' => 3],
            ['nama' => 'Sistem Operasi', 'kode' => 'TI320', 'sks' => 3, 'dosen_id' => 2],
            ['nama' => 'Struktur Data', 'kode' => 'TI303', 'sks' => 3, 'dosen_id' => 1],
            ['nama' => 'Algoritma', 'kode' => 'TI305', 'sks' => 3, 'dosen_id' => 1],
            ['nama' => 'Machine Learning', 'kode' => 'TI325', 'sks' => 3, 'dosen_id' => 3],
            ['nama' => 'Cloud Computing', 'kode' => 'TI330', 'sks' => 3, 'dosen_id' => 2],
            
            // Umum
            ['nama' => 'Bahasa Indonesia', 'kode' => 'TI333', 'sks' => 2, 'dosen_id' => 2],
            ['nama' => 'Bahasa Inggris', 'kode' => 'MMB01', 'sks' => 2, 'dosen_id' => 2],
            ['nama' => 'Sistem Komunikasi', 'kode' => 'MMB02', 'sks' => 2, 'dosen_id' => 3],
            ['nama' => 'Pancasila', 'kode' => 'MMB03', 'sks' => 2, 'dosen_id' => 1],
            ['nama' => 'Etika Bisnis', 'kode' => 'MMB04', 'sks' => 2, 'dosen_id' => 2],
            
            // Multimedia
            ['nama' => 'Desain Grafis', 'kode' => 'MM101', 'sks' => 3, 'dosen_id' => 1],
            ['nama' => 'Video Editing', 'kode' => 'MM102', 'sks' => 3, 'dosen_id' => 2],
            ['nama' => 'Animasi 3D', 'kode' => 'MM103', 'sks' => 3, 'dosen_id' => 3],
            ['nama' => 'Fotografi Digital', 'kode' => 'MM104', 'sks' => 2, 'dosen_id' => 1],
            ['nama' => 'Web Design', 'kode' => 'MM105', 'sks' => 3, 'dosen_id' => 2],
        ];

        // Insert Mata Kuliah jika belum ada
        foreach ($mataKuliah as $mk) {
            DB::table('mata_kuliah')->updateOrInsert(
                ['kode' => $mk['kode']],
                $mk
            );
        }

        // Get all kelas
        $kelas = DB::table('kelas')->selectRaw('id, nama_kelas, semester, prodi')->get();
        $dosen = DB::table('dosen')->get();
        $matkul = DB::table('mata_kuliah')->get();

        // Clear existing kelas_matkul dan jadwal untuk re-seed
        DB::table('kelas_matkul')->truncate();
        DB::table('jadwal')->truncate();

        // Link Mata Kuliah ke Kelas berdasarkan prodi
        foreach ($kelas as $k) {
            // Filter matkul berdasarkan prodi
            if (strpos($k->prodi, 'Teknik Informatika') !== false) {
                // Ambil TI courses + general courses
                $matkulIds = $matkul->filter(function($m) {
                    return strpos($m->kode, 'TI') === 0 || strpos($m->kode, 'MMB') === 0 || $m->kode == 'TI333';
                })->pluck('id');
            } elseif (strpos($k->prodi, 'Multimedia') !== false) {
                // Ambil MM courses + general courses
                $matkulIds = $matkul->filter(function($m) {
                    return strpos($m->kode, 'MM') === 0;
                })->pluck('id');
            } else {
                // Ambil general courses
                $matkulIds = $matkul->filter(function($m) {
                    return strpos($m->kode, 'MMB') === 0;
                })->pluck('id');
            }

            // Ambil 4-6 mata kuliah untuk setiap kelas
            $selectedMatkul = $matkulIds->random(min(6, $matkulIds->count()));
            
            foreach ($selectedMatkul as $mkId) {
                DB::table('kelas_matkul')->insert([
                    'kelas_id' => $k->id,
                    'matkul_id' => $mkId
                ]);
            }
        }

        // Create Jadwal untuk setiap kelas_matkul combination
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $jamList = [
            '08:00' => '09:30',
            '09:30' => '11:00',
            '11:00' => '12:30',
            '13:00' => '14:30',
            '14:30' => '16:00',
            '16:00' => '17:30',
        ];

        $kelasMatkul = DB::table('kelas_matkul')->get();
        $ruanganList = ['A101', 'A102', 'A103', 'A104', 'A105', 'B201', 'B202', 'B203', 'LAB1', 'LAB2', 'LAB3'];

        $jadwalCounter = 0;
        foreach ($kelasMatkul as $km) {
            $dosenId = $dosen->random()->id;
            $hari = $hariList[$jadwalCounter % count($hariList)];
            $jamKey = array_keys($jamList)[$jadwalCounter % count($jamList)];
            $jamMulai = $jamKey;
            $jamSelesai = $jamList[$jamKey];
            $ruangan = $ruanganList[$jadwalCounter % count($ruanganList)];

            DB::table('jadwal')->insert([
                'kelas_id' => $km->kelas_id,
                'matkul_id' => $km->matkul_id,
                'dosen_id' => $dosenId,
                'hari' => $hari,
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'ruangan' => $ruangan,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $jadwalCounter++;
        }

        echo "✅ Sample data updated successfully!\n";
        echo "   - Mata Kuliah: " . DB::table('mata_kuliah')->count() . "\n";
        echo "   - Kelas-Matkul Links: " . DB::table('kelas_matkul')->count() . "\n";
        echo "   - Jadwal: " . DB::table('jadwal')->count() . "\n";
    }
}
