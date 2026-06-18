<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\MahasiswaWebController;
use App\Http\Controllers\Web\NilaiWebController;
use App\Http\Controllers\Web\AbsensiWebController;
use App\Http\Controllers\Web\MataKuliahWebController;
use App\Http\Controllers\Web\KelasWebController;
use App\Http\Controllers\Web\DosenWebController;
use App\Http\Controllers\Web\JadwalWebController;
use App\Http\Controllers\Web\RapotWebController;
use App\Http\Controllers\SesiAbsensiController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/lifestyle-assessment', function () {
    return view('lifestyle_assessment');
});

Route::get('/lifestyle', function () {
    return view('lifestyle');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [
    DashboardController::class,
    'login'
])->name('login.post');

Route::post('/logout', [
    DashboardController::class,
    'logout'
])->name('logout');

Route::middleware('auth')->group(function () {

    // =========================
    // DASHBOARD
    // =========================

    Route::get('/dashboard', [
        DashboardController::class,
        'index'
    ])->name('dashboard');

    // =========================
    // RESOURCE
    // =========================

    Route::resource('mahasiswa', MahasiswaWebController::class);
    Route::resource('dosen', DosenWebController::class);
    Route::resource('matakuliah', MataKuliahWebController::class);
    Route::resource('nilai', NilaiWebController::class);
    Route::resource('absensi', AbsensiWebController::class);
    Route::resource('kelas', KelasWebController::class);
    
    // =========================
    // AJAX INTERNAL API
    // =========================

    /*
    |--------------------------------------------------------------------------
    | Ambil Mata Kuliah berdasarkan Kelas
    |--------------------------------------------------------------------------
    | Dipakai AJAX pada form buka sesi absensi
    | URL:
    | /api-internal/kelas/{id}/matkul
    |--------------------------------------------------------------------------
    */

    Route::get('/api-internal/kelas/{id}/matkul', function ($id) {
    $user  = \Illuminate\Support\Facades\Auth::user();
    $kelas = \App\Models\Kelas::with('mataKuliah')->findOrFail($id);
    $matkul = $kelas->mataKuliah;

    if ($user->role === 'dosen') {
        $dosen  = $user->dosen;
        $matkul = $dosen
            ? $matkul->where('dosen_id', $dosen->id)->values()
            : collect();
    }

    return response()->json($matkul->map(fn($m) => [
        'id'   => $m->id,
        'nama' => $m->nama,
        'kode' => $m->kode,
    ]));
})->middleware('auth');

    // =========================
    // KELAS
    // =========================

    Route::post('/kelas/{id}/mahasiswa', [
        KelasWebController::class,
        'tambahMahasiswa'
    ])->name('kelas.tambahMahasiswa');

    Route::delete('/kelas/{kelas_id}/mahasiswa/{mahasiswa_id}', [
        KelasWebController::class,
        'hapusMahasiswa'
    ])->name('kelas.hapusMahasiswa');

    Route::post('/kelas/{id}/assign-matkul', [
        KelasWebController::class,
        'assignMatkul'
    ])->name('kelas.assignMatkul');

    // =========================
    // JADWAL
    // =========================

    Route::get('/jadwal', [
        JadwalWebController::class,
        'index'
    ])->name('jadwal.index');

    Route::get('/jadwal/create', [
        JadwalWebController::class,
        'create'
    ])->name('jadwal.create');

    Route::post('/jadwal', [
        JadwalWebController::class,
        'store'
    ])->name('jadwal.store');

    Route::get('/jadwal/{id}/edit', [
        JadwalWebController::class,
        'edit'
    ])->name('jadwal.edit');

    Route::put('/jadwal/{id}', [
        JadwalWebController::class,
        'update'
    ])->name('jadwal.update');

    Route::delete('/jadwal/{id}', [
        JadwalWebController::class,
        'destroy'
    ])->name('jadwal.destroy');

    // =========================
    // RAPOT
    // =========================

    Route::get('/rapot', [
        RapotWebController::class,
        'index'
    ])->name('rapot.index');

    // =========================
    // SESI ABSENSI
    // =========================

    Route::get('/sesi-absensi', [
        SesiAbsensiController::class,
        'index'
    ])->name('sesi-absensi.index');

    Route::post('/sesi-absensi', [
        SesiAbsensiController::class,
        'store'
    ])->name('sesi-absensi.store');

    Route::patch('/sesi-absensi/{id}/tutup', [
        SesiAbsensiController::class,
        'tutup'
    ])->name('sesi-absensi.tutup');

    Route::delete('/sesi-absensi/{id}', [
        SesiAbsensiController::class,
        'destroy'
    ])->name('sesi-absensi.destroy');

    // =========================
    // USERS
    // =========================

    Route::get('/users', [
        DashboardController::class,
        'userList'
    ])->name('users.index');

    Route::get('/users/create', [
        DashboardController::class,
        'createUser'
    ])->name('users.create');

    Route::post('/users', [
        DashboardController::class,
        'storeUser'
    ])->name('users.store');

    Route::get('/users/{id}/edit', [
        DashboardController::class,
        'editUser'
    ])->name('users.edit');

    Route::put('/users/{id}', [
        DashboardController::class,
        'updateUser'
    ])->name('users.update');

    Route::delete('/users/{id}', [
        DashboardController::class,
        'destroyUser'
    ])->name('users.destroy');

});

Route::view('/lifestyle', 'lifestyle');