<?php

namespace App\Console\Commands;

use App\Models\SesiAbsensi;
use Illuminate\Console\Command;

class TutupSesiAbsensi extends Command {
    protected $signature   = 'sesi:tutup';
    protected $description = 'Auto tutup sesi absensi yang sudah lewat jam tutup';

    public function handle() {
        $sekarang = now()->format('H:i');

        $sesi = SesiAbsensi::where('status', 'buka')
                    ->whereNotNull('jam_tutup')
                    ->where('jam_tutup', '<=', $sekarang)
                    ->get();

        foreach ($sesi as $s) {
            $s->update(['status' => 'tutup']);
            $this->info("Sesi ID {$s->id} ditutup otomatis.");
        }

        $this->info("Total ditutup: {$sesi->count()} sesi.");
    }
}