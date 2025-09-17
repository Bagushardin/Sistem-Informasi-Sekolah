<?php

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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthSiswaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Route utama
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home');
    }
    return redirect()->route('login');
});

// Authentication routes
Auth::routes();

// Route::get('/debug-user', function () {
//     if (Auth::check()) {
//         $user = Auth::user();
//         return response()->json([
//             'id' => $user->id,
//             'name' => $user->name,
//             'email' => $user->email,
//             'role' => $user->roles ?? 'no_role',
//             'roles' => $user->roles ?? 'no_roles'
//         ]);
//     }
//     return 'Not authenticated';
// })->middleware('auth');

// Home route
Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware('auth');

// Profile routes
Route::group(['middleware' => 'auth'], function () {
    Route::get('/profile', [UserController::class, 'edit'])->name('profile');
    Route::put('/update-profile', [UserController::class, 'update'])->name('update.profile');
    Route::get('/edit-password', [UserController::class, 'editPassword'])->name('ubah-password');
    Route::patch('/update-password', [UserController::class, 'updatePassword'])->name('update-password');
});


// add Siswa Authentication routes
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

// TEMPORARY: Admin routes tanpa middleware role
Route::group(['middleware' => 'auth'], function () {
    Route::resource('jurusan', JurusanController::class);
    Route::resource('mapel', MapelController::class);
    Route::resource('guru', GuruController::class);
    Route::resource('kelas', KelasController::class);
    Route::resource('siswa', SiswaController::class);
    Route::resource('user', UserController::class);
    Route::resource('jadwal', JadwalController::class);
    Route::resource('pengumuman-sekolah', PengumumanSekolahController::class);
    Route::resource('pengaturan', PengaturanController::class);
    
    // Admin Absensi routes
    Route::prefix('admin/absensi')->name('admin.absensi.')->group(function () {
        Route::get('/', [AbsensiController::class, 'adminIndex'])->name('index');
        Route::post('/buka', [AbsensiController::class, 'bukaAbsensi'])->name('buka');
        Route::patch('/{id}/tutup', [AbsensiController::class, 'tutupAbsensi'])->name('tutup');
    });
});

// TEMPORARY: Guru routes tanpa middleware role
Route::group(['middleware' => 'auth'], function () {
    Route::resource('materi', MateriController::class);
    Route::resource('tugas', TugasController::class);
    Route::get('/jawaban-download/{id}', [TugasController::class, 'downloadJawaban'])->name('guru.jawaban.download');
    
    Route::prefix('guru/absensi')->name('guru.absensi.')->group(function () {
        Route::get('/', [AbsensiController::class, 'guruIndex'])->name('index');
        Route::get('/{sesi}/absen', [AbsensiController::class, 'guruAbsen'])->name('absen');
        Route::put('/{sesi}/update', [AbsensiController::class, 'updateAbsensi'])->name('update');
    });
});

// TEMPORARY: Siswa routes tanpa middleware role
Route::group(['middleware' => 'auth'], function () {
    Route::get('/siswa/materi', [MateriController::class, 'siswa'])->name('siswa.materi');
    Route::get('/materi-download/{id}', [MateriController::class, 'download'])->name('siswa.materi.download');
    Route::get('/siswa/tugas', [TugasController::class, 'siswa'])->name('siswa.tugas');
    Route::get('/tugas-download/{id}', [TugasController::class, 'download'])->name('siswa.tugas.download');
    Route::post('/kirim-jawaban', [TugasController::class, 'kirimJawaban'])->name('kirim-jawaban');
    
    Route::prefix('siswa/absensi')->name('siswa.absensi.')->group(function () {
        Route::get('/', [AbsensiController::class, 'siswaIndex'])->name('index');
        Route::get('/mapel/{mapel}', [AbsensiController::class, 'siswaDetail'])->name('detail');
    });
});

// TEMPORARY: Orangtua routes tanpa middleware role
Route::group(['middleware' => 'auth'], function () {
    Route::get('/orangtua/tugas/siswa', [TugasController::class, 'orangtua'])->name('orangtua.tugas.siswa');
});