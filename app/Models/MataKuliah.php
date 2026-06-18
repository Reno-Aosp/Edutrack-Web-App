<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MataKuliah extends Model {
    protected $table = 'mata_kuliah';

    protected $fillable = [
        'nama', 'kode', 'sks', 'dosen_id'
    ];

    public function dosen() {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    public function kelas() {
        return $this->belongsToMany(Kelas::class, 'kelas_matkul', 'matkul_id', 'kelas_id');
    }

    public function nilai() {
        return $this->hasMany(Nilai::class, 'matkul_id');
    }

    public function absensi() {
        return $this->hasMany(Absensi::class, 'matkul_id');
    }
}