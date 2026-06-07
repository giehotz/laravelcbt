<?php

use App\Http\Controllers\Siswa\DashboardController;
use Illuminate\Support\Facades\Route;

// Dashboard siswa utama
Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

// JSON endpoints untuk polling
Route::get('dashboard/jadwal-hari-ini', [DashboardController::class, 'jadwalHariIni'])->name('dashboard.jadwal');
Route::get('dashboard/pengumuman', [DashboardController::class, 'pengumuman'])->name('dashboard.pengumuman');

// Daftar ujian tersedia untuk siswa (link dari menu Ujian/Ulangan)
Route::get('ujian', [DashboardController::class, 'daftarUjian'])->name('ujian');

// Menu lainnya (placeholder/sederhana)
Route::get('jadwal', [DashboardController::class, 'jadwalPelajaran'])->name('jadwal');
Route::get('tugas', [DashboardController::class, 'tugas'])->name('tugas');
Route::get('nilai', [DashboardController::class, 'nilaiHasil'])->name('nilai');
Route::get('absensi', [DashboardController::class, 'absensi'])->name('absensi');
Route::get('catatan-guru', [DashboardController::class, 'catatanGuru'])->name('catatan-guru');
