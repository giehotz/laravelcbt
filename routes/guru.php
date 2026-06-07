<?php

use App\Http\Controllers\Guru\GuruAnalisisSoalController;
use App\Http\Controllers\Guru\GuruCetakController;
use App\Http\Controllers\Guru\GuruHasilUjianController;
use App\Http\Controllers\Guru\GuruRekapNilaiController;
use App\Http\Controllers\Guru\GuruStatusSiswaController;
use App\Http\Controllers\Guru\GuruUlanganController;
use Illuminate\Support\Facades\Route;

// Fase 1: Cetak — Dokumen Administratif Ujian
Route::get('cetak', [GuruCetakController::class, 'index'])->name('cetak');
Route::get('cetak/kartu', [GuruCetakController::class, 'kartu'])->name('cetak.kartu');
Route::get('cetak/daftar-hadir', [GuruCetakController::class, 'daftarHadir'])->name('cetak.daftar-hadir');
Route::get('cetak/berita-acara', [GuruCetakController::class, 'beritaAcara'])->name('cetak.berita-acara');
Route::get('cetak/rekap-nilai', [GuruCetakController::class, 'rekapNilai'])->name('cetak.rekap-nilai');

// Fase 2: Status Siswa — Monitor Real-time
Route::get('status-siswa', [GuruStatusSiswaController::class, 'index'])->name('status-siswa');
Route::get('status-siswa/{jadwal}/data', [GuruStatusSiswaController::class, 'data'])->name('status-siswa.data');

// Fase 3: Hasil Ujian — Koreksi Esai + Nilai
Route::get('hasil-ujian', [GuruHasilUjianController::class, 'index'])->name('hasil-ujian');
Route::get('hasil-ujian/{jadwal}/nilai', [GuruHasilUjianController::class, 'nilai'])->name('hasil-ujian.nilai');

// Fase 4: Rekap Nilai — Agregat per Kelas & Mapel
Route::get('rekap-nilai', [GuruRekapNilaiController::class, 'index'])->name('rekap-nilai');
Route::get('rekap-nilai/data', [GuruRekapNilaiController::class, 'data'])->name('rekap-nilai.data');

// Fase 5: Analisis Soal — Per-butir
Route::get('analisis-soal', [GuruAnalisisSoalController::class, 'index'])->name('analisis-soal');
Route::get('analisis-soal/{jadwal}/data', [GuruAnalisisSoalController::class, 'data'])->name('analisis-soal.data');

// Fase 6: Dashboard Ulangan/Ujian
Route::get('ulangan-ujian', [GuruUlanganController::class, 'index'])->name('ulangan-ujian');
