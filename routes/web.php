<?php

use App\Http\Controllers\SettingController;
use App\Http\Controllers\Setting\AdminUserController;
use App\Http\Controllers\Setting\GuruUserController;
use App\Http\Controllers\Setting\GuruAssignmentController;
use App\Http\Controllers\Setting\SiswaUserController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('pengumuman', \App\Http\Controllers\PengumumanController::class)->except(['show', 'edit', 'update']);

    Route::prefix('lms')->name('lms.')->group(function () {
        Route::post('materi/{materi}/log-baca', [\App\Http\Controllers\Lms\MateriController::class, 'logBaca'])->name('materi.log-baca');
        Route::resource('materi', \App\Http\Controllers\Lms\MateriController::class);
    });
});

// Master routes (Academic periods)
Route::middleware(['auth', 'verified', 'role:superadmin|operator'])
    ->prefix('master')->name('master.')
    ->group(base_path('routes/master.php'));

// CBT Setup routes
Route::middleware(['auth', 'verified', 'role:superadmin|operator|proktor|guru|kepsek'])
    ->prefix('cbt')->name('cbt.')
    ->group(base_path('routes/cbt.php'));

// Setting & User Management routes
Route::middleware(['auth', 'verified'])->group(function () {
    // School Settings (Super Admin only)
    Route::middleware(['role:superadmin'])->group(function () {
        Route::get('setting/sekolah', [SettingController::class, 'edit'])->name('setting.sekolah.edit');
        Route::post('setting/sekolah', [SettingController::class, 'update'])->name('setting.sekolah.update');
        
        // Database Utilities
        Route::get('utility/database', [\App\Http\Controllers\Utility\DatabaseController::class, 'index'])->name('utility.database.index');
        Route::post('utility/database/backup', [\App\Http\Controllers\Utility\DatabaseController::class, 'backup'])->name('utility.database.backup');
        Route::post('utility/database/truncate', [\App\Http\Controllers\Utility\DatabaseController::class, 'truncate'])->name('utility.database.truncate');
        
        // Rapor Settings
        Route::get('rapor/setting', [\App\Http\Controllers\Rapor\RaporController::class, 'setting'])->name('rapor.setting');
        Route::post('rapor/setting', [\App\Http\Controllers\Rapor\RaporController::class, 'updateSetting'])->name('rapor.setting.update');
    });

    // Rapor (Guru, Admin)
    Route::middleware(['role:superadmin|operator|guru|kepsek'])->group(function () {
        Route::get('rapor/input-nilai', [\App\Http\Controllers\Rapor\RaporController::class, 'inputNilai'])->name('rapor.input_nilai');
        Route::post('rapor/import-cbt', [\App\Http\Controllers\Rapor\RaporController::class, 'importCbt'])->name('rapor.import_cbt');
        Route::post('rapor/simpan-nilai', [\App\Http\Controllers\Rapor\RaporController::class, 'simpanNilai'])->name('rapor.simpan_nilai');
        Route::post('rapor/kunci/{kelas}/{mapel}', [\App\Http\Controllers\Rapor\RaporController::class, 'kunci'])->name('rapor.kunci');
        Route::get('rapor/cetak-siswa/{siswa}', [\App\Http\Controllers\Rapor\RaporController::class, 'cetakSiswa'])->name('rapor.cetak_siswa');
        Route::get('rapor/cetak-kelas/{kelas}', [\App\Http\Controllers\Rapor\RaporController::class, 'cetakKelas'])->name('rapor.cetak_kelas');
    });

    // User Management (Super Admin and Operator)
    Route::middleware(['role:superadmin|operator'])->prefix('setting/user')->name('setting.user.')->group(function () {
        Route::resource('admin', AdminUserController::class)->except(['create', 'show', 'edit']);

        Route::get('guru/template', [GuruUserController::class, 'downloadTemplate'])->name('guru.template');
        Route::post('guru/import', [GuruUserController::class, 'import'])->name('guru.import');
        Route::get('guru/{guru}/assignment', [GuruAssignmentController::class, 'edit'])->name('guru.assignment.edit');
        Route::put('guru/{guru}/assignment', [GuruAssignmentController::class, 'update'])->name('guru.assignment.update');
        Route::resource('guru', GuruUserController::class)->except(['create', 'show', 'edit']);

        Route::get('siswa/template', [SiswaUserController::class, 'downloadTemplate'])->name('siswa.template');
        Route::post('siswa/import', [SiswaUserController::class, 'import'])->name('siswa.import');
        Route::resource('siswa', SiswaUserController::class)->except(['create', 'show', 'edit']);
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/ujian.php';
