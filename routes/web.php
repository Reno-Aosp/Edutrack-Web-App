<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\MahasiswaWebController;
use App\Http\Controllers\Web\NilaiWebController;
use App\Http\Controllers\Web\AbsensiWebController;
use App\Http\Controllers\Web\MataKuliahWebController;
use App\Http\Controllers\Web\KelasWebController;
// Login
Route::get('/', function () {
    return redirect()->route('login');
});
Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::post('/login', [DashboardController::class, 'login'])->name('login.post');
Route::post('/logout', [DashboardController::class, 'logout'])->name('logout');
// Protected web routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Mahasiswa
    Route::resource('mahasiswa', MahasiswaWebController::class);
    // Mata Kuliah
    Route::resource('matakuliah', MataKuliahWebController::class);
    // Nilai
    Route::resource('nilai', NilaiWebController::class);
    // Absensi
    Route::resource('absensi', AbsensiWebController::class);
    // Kelas
    Route::resource('kelas', KelasWebController::class);
    Route::post('/kelas/{id}/mahasiswa', [KelasWebController::class, 'tambahMahasiswa'])->name('kelas.tambahMahasiswa');
    Route::delete('/kelas/{kelas_id}/mahasiswa/{mahasiswa_id}', [KelasWebController::class, 'hapusMahasiswa'])->name('kelas.hapusMahasiswa');
    // User Management
    Route::get('/users', [DashboardController::class, 'userList'])->name('users.index');
    Route::get('/users/create', [DashboardController::class, 'createUser'])->name('users.create');
    Route::post('/users', [DashboardController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{id}/edit', [DashboardController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{id}', [DashboardController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}', [DashboardController::class, 'destroyUser'])->name('users.destroy');
});