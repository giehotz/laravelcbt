<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Lms\MateriController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\Rapor\RaporController;
use App\Http\Controllers\Setting\AdminUserController;
use App\Http\Controllers\Setting\GuruAssignmentController;
use App\Http\Controllers\Setting\GuruUserController;
use App\Http\Controllers\Setting\SiswaUserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Utility\DatabaseController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('pengumuman', PengumumanController::class)->except(['show', 'edit', 'update']);

    Route::prefix('lms')->name('lms.')->group(function () {
        Route::post('materi/{materi}/log-baca', [MateriController::class, 'logBaca'])->name('materi.log-baca');
        Route::resource('materi', MateriController::class);
    });

    // Guru routes — dipisah ke routes/guru.php
    Route::middleware(['role:superadmin|operator|guru|kepsek|proktor'])
        ->prefix('guru')->name('guru.')
        ->group(base_path('routes/guru.php'));
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
        Route::get('utility/database', [DatabaseController::class, 'index'])->name('utility.database.index');
        Route::post('utility/database/backup', [DatabaseController::class, 'backup'])->name('utility.database.backup');
        Route::post('utility/database/truncate', [DatabaseController::class, 'truncate'])->name('utility.database.truncate');

        // Rapor Settings
        Route::get('rapor/setting', [RaporController::class, 'setting'])->name('rapor.setting');
        Route::post('rapor/setting', [RaporController::class, 'updateSetting'])->name('rapor.setting.update');
    });

    // Rapor (Guru, Admin)
    Route::middleware(['role:superadmin|operator|guru|kepsek'])->group(function () {
        Route::get('rapor/input-nilai', [RaporController::class, 'inputNilai'])->name('rapor.input_nilai');
        Route::post('rapor/import-cbt', [RaporController::class, 'importCbt'])->name('rapor.import_cbt');
        Route::post('rapor/simpan-nilai', [RaporController::class, 'simpanNilai'])->name('rapor.simpan_nilai');
        Route::post('rapor/kunci/{kelas}/{mapel}', [RaporController::class, 'kunci'])->name('rapor.kunci');
        Route::get('rapor/cetak-siswa/{siswa}', [RaporController::class, 'cetakSiswa'])->name('rapor.cetak_siswa');
        Route::get('rapor/cetak-kelas/{kelas}', [RaporController::class, 'cetakKelas'])->name('rapor.cetak_kelas');
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

// Siswa routes (mobile-first, header-only layout)
Route::middleware(['auth', 'verified', 'role:siswa'])
    ->prefix('siswa')->name('siswa.')
    ->group(base_path('routes/siswa.php'));
