<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CreateJadwalTableSeeder extends Seeder
{
    public function run(): void
    {
        // Create jadwal table if it doesn't exist
        DB::statement("CREATE TABLE IF NOT EXISTS `jadwal` (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `kelas_id` bigint unsigned NOT NULL,
            `matkul_id` bigint unsigned NOT NULL,
            `dosen_id` bigint unsigned DEFAULT NULL,
            `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL,
            `jam_mulai` time NOT NULL,
            `jam_selesai` time NOT NULL,
            `ruangan` varchar(50) DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `jadwal_kelas_id_foreign` (`kelas_id`),
            KEY `jadwal_matkul_id_foreign` (`matkul_id`),
            KEY `jadwal_dosen_id_foreign` (`dosen_id`),
            CONSTRAINT `jadwal_dosen_id_foreign` FOREIGN KEY (`dosen_id`) REFERENCES `dosen` (`id`) ON DELETE SET NULL,
            CONSTRAINT `jadwal_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
            CONSTRAINT `jadwal_matkul_id_foreign` FOREIGN KEY (`matkul_id`) REFERENCES `mata_kuliah` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
}
