<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nilai extends Model {
    protected $table = 'nilai';
    protected $fillable = [
        'mahasiswa_id', 'matkul_id', 'kelas_id',
        'nilai_tugas', 'nilai_uts', 'nilai_uas',
        'nilai_akhir', 'semester'
    ];

    public function mahasiswa() {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function mataKuliah() {
        return $this->belongsTo(MataKuliah::class, 'matkul_id');
    }

    public function kelas() {
        return $this->belongsTo(Kelas::class);
    }
}