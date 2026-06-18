<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sesi_absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matkul_id')->constrained('mata_kuliah')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('dosen_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal');
            $table->time('jam_buka')->nullable();
            $table->time('jam_tutup')->nullable();
            $table->enum('status', ['buka', 'tutup'])->default('buka');
            $table->string('pertemuan_ke')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('sesi_absensi');
    }
};