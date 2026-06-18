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

Route::get('/', function () {
    return redirect()->route('login');
});
Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::post('/login', [DashboardController::class, 'login'])->name('login.post');
Route::post('/logout', [DashboardController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('mahasiswa', MahasiswaWebController::class);
    Route::resource('dosen', DosenWebController::class);
    Route::resource('matakuliah', MataKuliahWebController::class);
    Route::resource('nilai', NilaiWebController::class);
    Route::resource('rapot', RapotWebController::class)->only(['index', 'show']);
    Route::resource('absensi', AbsensiWebController::class);
    Route::resource('kelas', KelasWebController::class);

    Route::post('/kelas/{id}/mahasiswa', [KelasWebController::class, 'tambahMahasiswa'])->name('kelas.tambahMahasiswa');
    Route::delete('/kelas/{kelas_id}/mahasiswa/{mahasiswa_id}', [KelasWebController::class, 'hapusMahasiswa'])->name('kelas.hapusMahasiswa');
    Route::post('/kelas/{id}/assign-matkul', [KelasWebController::class, 'assignMatkul'])->name('kelas.assignMatkul');

    // Jadwal
    Route::get('/jadwal', [JadwalWebController::class, 'index'])->name('jadwal.index');
    Route::get('/jadwal/create', [JadwalWebController::class, 'create'])->name('jadwal.create');
    Route::post('/jadwal', [JadwalWebController::class, 'store'])->name('jadwal.store');
    Route::get('/jadwal/{id}/edit', [JadwalWebController::class, 'edit'])->name('jadwal.edit');
    Route::put('/jadwal/{id}', [JadwalWebController::class, 'update'])->name('jadwal.update');
    Route::delete('/jadwal/{id}', [JadwalWebController::class, 'destroy'])->name('jadwal.destroy');

    // Users
    Route::get('/users', [DashboardController::class, 'userList'])->name('users.index');
    Route::get('/users/create', [DashboardController::class, 'createUser'])->name('users.create');
    Route::post('/users', [DashboardController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{id}/edit', [DashboardController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{id}', [DashboardController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}', [DashboardController::class, 'destroyUser'])->name('users.destroy');
});