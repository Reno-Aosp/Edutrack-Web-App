<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dosen extends Model {
    protected $table = 'dosen';

    protected $fillable = [
        'user_id', 'nidn'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function mataKuliah() {
        return $this->hasMany(MataKuliah::class, 'dosen_id');
    }
}