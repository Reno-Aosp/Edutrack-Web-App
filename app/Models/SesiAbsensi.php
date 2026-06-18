<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SesiAbsensi extends Model {
    protected $table = 'sesi_absensi';
    protected $fillable = [
        'matkul_id', 'kelas_id', 'dosen_id',
        'tanggal', 'jam_buka', 'jam_tutup',
        'pertemuan_ke', 'status',
    ];

    public function mataKuliah() {
        return $this->belongsTo(MataKuliah::class, 'matkul_id');
    }

    public function kelas() {
        return $this->belongsTo(Kelas::class);
    }

    public function dosen() {
        return $this->belongsTo(User::class, 'dosen_id');
    }
}