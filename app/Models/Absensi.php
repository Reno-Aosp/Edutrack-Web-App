<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model {
    protected $table = 'absensi';

    protected $fillable = [
        'mahasiswa_id', 'matkul_id',
        'tanggal', 'status', 'keterangan'
    ];

    public function mahasiswa() {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function mataKuliah() {
        return $this->belongsTo(MataKuliah::class, 'matkul_id');
    }
}