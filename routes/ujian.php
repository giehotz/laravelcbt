<?php

use App\Http\Controllers\Siswa\CbtUjianController;
use Illuminate\Support\Facades\Route;

// Ujian routes (Siswa only)
Route::middleware(['auth', 'verified', 'role:siswa'])->prefix('ujian')->name('ujian.')->group(function () {

    // Middlewares:
    // 1. check.exam.session: memvalidasi jadwal, mac address, status durasi
    Route::middleware(['check.exam.session'])->group(function () {

        // Halaman antarmuka utama ujian (Inertia)
        Route::get('/{jadwal}', [CbtUjianController::class, 'show'])->name('show');

        // API untuk mengambil soal
        Route::get('/{jadwal}/soal', [CbtUjianController::class, 'getSoal'])->name('soal');

        // API untuk menyimpan jawaban tunggal
        Route::post('/soal-siswa/{soalSiswa}/simpan', [CbtUjianController::class, 'simpanJawaban'])->name('simpan');

    });

    // Mulai ujian (set start time & distribusi soal jika belum)
    // Tidak diproteksi check.exam.session karena di titik ini durasi belum terbentuk
    Route::post('/{jadwal}/mulai', [CbtUjianController::class, 'mulaiUjian'])->name('mulai');

    // Selesai ujian (menutup sesi)
    Route::post('/{jadwal}/selesai', [CbtUjianController::class, 'selesaiUjian'])->name('selesai');
});
