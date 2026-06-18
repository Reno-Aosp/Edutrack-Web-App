<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = [
        'mahasiswa_id',
        'matkul_id',
        'kelas_id',
        'tanggal',
        'status',
        'keterangan',
        'foto_url',   // ← URL foto surat dari Supabase Storage
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'matkul_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
}
