<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nilai extends Model {
    protected $table = 'nilai';

    protected $fillable = [
        'mahasiswa_id', 'matkul_id', 
        'nilai_tugas', 'nilai_uts', 'nilai_uas', 
        'nilai_akhir', 'semester'
    ];

    public function mahasiswa() {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function mataKuliah() {
        return $this->belongsTo(MataKuliah::class, 'matkul_id');
    }

    // Hitung nilai akhir otomatis sebelum disimpan
    protected static function boot() {
        parent::boot();
        static::saving(function ($nilai) {
            $nilai->nilai_akhir = 
                ($nilai->nilai_tugas * 0.3) + 
                ($nilai->nilai_uts * 0.3) + 
                ($nilai->nilai_uas * 0.4);
        });
    }
}