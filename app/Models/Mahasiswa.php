<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model {
    protected $table = 'mahasiswa';
    protected $fillable = ['user_id', 'nama', 'nim', 'prodi', 'angkatan'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function kelas() {
        return $this->belongsToMany(Kelas::class, 'kelas_mahasiswa');
    }

    public function nilai() {
        return $this->hasMany(Nilai::class);
    }

    public function absensi() {
        return $this->hasMany(Absensi::class);
    }
}