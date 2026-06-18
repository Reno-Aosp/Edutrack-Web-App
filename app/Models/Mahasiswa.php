<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model {
    protected $table = 'mahasiswa';
    
    protected $fillable = [
        'user_id', 'nim', 'prodi', 'angkatan'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function nilai() {
        return $this->hasMany(Nilai::class, 'mahasiswa_id');
    }

    public function absensi() {
        return $this->hasMany(Absensi::class, 'mahasiswa_id');
    }
}