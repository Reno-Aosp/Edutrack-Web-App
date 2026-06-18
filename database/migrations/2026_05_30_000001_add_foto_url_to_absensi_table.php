<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom foto_url ke tabel absensi
     * untuk menyimpan URL foto surat izin/sakit dari Supabase Storage
     */
    public function up(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->string('foto_url')->nullable()->after('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropColumn('foto_url');
        });
    }
};
