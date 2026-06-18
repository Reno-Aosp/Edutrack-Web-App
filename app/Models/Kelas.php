<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model {
    protected $table = 'kelas';
    protected $fillable = ['nama_kelas', 'prodi', 'angkatan', 'semester'];

    public function mahasiswa() {
        return $this->belongsToMany(Mahasiswa::class, 'kelas_mahasiswa');
    }

    public function mataKuliah() {
        return $this->belongsToMany(MataKuliah::class, 'kelas_matkul', 'kelas_id', 'matkul_id');
    }

    public function jadwal() {
        return $this->hasMany(Jadwal::class);
    }

    public function nilai() {
        return $this->hasMany(Nilai::class);
    }

    public function absensi() {
        return $this->hasMany(Absensi::class);
    }
}