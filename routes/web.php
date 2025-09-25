<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Middleware
use App\Http\Middleware\CheckRole;

// Controller imports
use App\Http\Controllers\GuruController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\MateriController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PengumumanSekolahController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\TugasController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AuthSiswaController;
use App\Http\Controllers\GuruAbsensiController;
use App\Http\Controllers\AuthGuruController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Root redirect
Route::get('/', function () {
    return Auth::check() ? redirect()->route('home') : redirect()->route('login');
});

// ==================== ROUTES TANPA AUTH ====================

// Auth untuk guru
Route::prefix('guru')->name('guru.')->group(function () {
    Route::get('/login', [AuthGuruController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthGuruController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AuthGuruController::class, 'logout'])->name('logout');
});

// Auth untuk siswa
Route::prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/login', [AuthSiswaController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthSiswaController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AuthSiswaController::class, 'logout'])->name('logout');
});

// Auth default Laravel
Auth::routes(['register' => false]);

// ==================== ROUTES DENGAN AUTH ====================

// Home route
Route::get('/home', [HomeController::class, 'index'])
    ->name('home')
    ->middleware('auth');

// Profile (semua user yang login)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [UserController::class, 'edit'])->name('profile');
    Route::put('/update-profile', [UserController::class, 'update'])->name('update.profile');
    Route::get('/edit-password', [UserController::class, 'editPassword'])->name('ubah-password');
    Route::patch('/update-password', [UserController::class, 'updatePassword'])->name('update-password');
});


// add Siswa Authentication routes here
Route::group(['prefix' => 'siswa'], function () {
    Route::get('/login', [AuthSiswaController::class, 'showLoginForm'])->name('siswa.login');
    Route::post('/login', [AuthSiswaController::class, 'login'])->name('siswa.login.submit');
    Route::post('/logout', [AuthSiswaController::class, 'logout'])->name('siswa.logout');
});


// TEMPORARY: Dashboard routes tanpa middleware role untuk debugging
Route::group(['middleware' => 'auth'], function () {
    Route::get('/admin/dashboard', [HomeController::class, 'admin'])->name('admin.dashboard');
    Route::get('/guru/dashboard', [HomeController::class, 'guru'])->name('guru.dashboard');
    Route::get('/siswa/dashboard', [HomeController::class, 'siswa'])->name('siswa.dashboard');
    Route::get('/orangtua/dashboard', [HomeController::class, 'orangtua'])->name('orangtua.dashboard');
});

// ==================== ADMIN ROUTES ====================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resources([
        'jurusan' => JurusanController::class,
        'mapel' => MapelController::class,
        'guru' => GuruController::class,
        'kelas' => KelasController::class,
        'siswa' => SiswaController::class,
        'user' => UserController::class,
        'jadwal' => JadwalController::class,
        'pengumuman-sekolah' => PengumumanSekolahController::class,
        'pengaturan' => PengaturanController::class,
    ]);

    // Absensi admin
    Route::prefix('absensi')->name('absensi.')->group(function () {
        Route::get('/', [AbsensiController::class, 'adminIndex'])->name('index');
        Route::post('/buka', [AbsensiController::class, 'bukaAbsensi'])->name('buka');
        Route::patch('/{id}/tutup', [AbsensiController::class, 'tutupAbsensi'])->name('tutup');
    });
});

// ==================== GURU ROUTES ====================
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::resource('materi', MateriController::class)->names('materi');
    Route::resource('tugas', TugasController::class)->names('tugas');

    Route::get('/jawaban-download/{id}', [TugasController::class, 'downloadJawaban'])->name('jawaban.download');
    
    Route::prefix('absensi')->name('absensi.')->group(function () {
        Route::get('/', [GuruAbsensiController::class, 'guruIndex'])->name('index');
        Route::get('/{sesi}/absen', [GuruAbsensiController::class, 'guruAbsen'])->name('absen');
        Route::put('/{sesi}/update', [GuruAbsensiController::class, 'updateAbsensi'])->name('update');
    });
});

// ==================== SISWA ROUTES ====================
Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/materi', [MateriController::class, 'siswa'])->name('materi');
    Route::get('/tugas', [TugasController::class, 'siswa'])->name('tugas');

    Route::get('/materi-download/{id}', [MateriController::class, 'download'])->name('materi.download');
    Route::get('/tugas-download/{id}', [TugasController::class, 'download'])->name('tugas.download');

    Route::post('/kirim-jawaban', [TugasController::class, 'kirimJawaban'])->name('kirim-jawaban');

    Route::prefix('absensi')->name('absensi.')->group(function () {
        Route::get('/', [AbsensiController::class, 'siswaIndex'])->name('index');
        Route::get('/mapel/{mapel}', [AbsensiController::class, 'siswaDetail'])->name('detail');
    });
});

// ==================== ORANGTUA ROUTES ====================
Route::middleware(['auth', 'role:orangtua'])->prefix('orangtua')->name('orangtua.')->group(function () {
    Route::get('/tugas/siswa', [TugasController::class, 'orangtua'])->name('tugas.siswa');
});
