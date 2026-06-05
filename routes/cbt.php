<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cbt\JenisUjianController;
use App\Http\Controllers\Cbt\SesiController;
use App\Http\Controllers\Cbt\RuangController;
use App\Http\Controllers\Cbt\TokenController;
use App\Http\Controllers\Cbt\JadwalController;
use App\Http\Controllers\Cbt\PengawasController;
use App\Http\Controllers\Cbt\NomorPesertaController;
use App\Http\Controllers\Cbt\AturRuangSesiController;
use App\Http\Controllers\Cbt\BankSoalController;
use App\Http\Controllers\Cbt\SoalController;
use App\Http\Controllers\Cbt\ProctorController;
use App\Http\Controllers\Cbt\KoreksiController;
use App\Http\Controllers\Cbt\ReportController;

/*
|--------------------------------------------------------------------------
| CBT Routes
|--------------------------------------------------------------------------
|
| Here is where you can register CBT related routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group.
|
*/

// Master Data CBT - Fase 3.1
Route::resource('jenis', JenisUjianController::class)->parameters(['jenis' => 'jenis'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('sesi', SesiController::class)->only(['index', 'store', 'update', 'destroy']);
Route::resource('ruang', RuangController::class)->only(['index', 'store', 'update', 'destroy']);

// Token Ujian
Route::prefix('token')->name('token.')->group(function () {
    Route::get('/', [TokenController::class, 'index'])->name('index');
    Route::post('/generate', [TokenController::class, 'generate'])->name('generate');
    Route::post('/auto', [TokenController::class, 'toggleAuto'])->name('toggleAuto');
});

// Pengawas
Route::prefix('pengawas')->name('pengawas.')->group(function () {
    Route::get('/', [PengawasController::class, 'index'])->name('index');
    Route::get('/{jadwal}', [PengawasController::class, 'show'])->name('show');
    Route::post('/{jadwal}/sync', [PengawasController::class, 'sync'])->name('sync');
});

// Jadwal Ujian
Route::post('jadwal/{jadwal}/toggle-status', [JadwalController::class, 'toggleStatus'])->name('jadwal.toggle-status');
Route::resource('jadwal', JadwalController::class)->except(['show']);
use App\Http\Controllers\Cbt\AlokasiWaktuController;

// Alokasi Waktu
Route::prefix('alokasi-waktu')->name('alokasi-waktu.')->group(function () {
    Route::get('/', [AlokasiWaktuController::class, 'index'])->name('index');
    Route::post('/{jadwal}/generate', [AlokasiWaktuController::class, 'generate'])->name('generate');
});

// Fase 3.2: Nomor Peserta
Route::get('nomor-peserta', [NomorPesertaController::class, 'index'])->name('nomor-peserta.index');
Route::post('nomor-peserta/generate', [NomorPesertaController::class, 'generate'])->name('nomor-peserta.generate');

// Fase 3.3: Atur Ruang/Sesi
Route::get('atur-ruang', [AturRuangSesiController::class, 'index'])->name('atur-ruang.index');
Route::post('atur-ruang', [AturRuangSesiController::class, 'store'])->name('atur-ruang.store');
Route::post('atur-ruang/{kelasRuang}/sync', [AturRuangSesiController::class, 'sync'])->name('atur-ruang.sync');

// Fase 3.4: Bank Soal & Soal
Route::resource('bank-soal', BankSoalController::class);
Route::prefix('bank-soal/{bank}')->name('bank-soal.')->group(function () {
    Route::resource('soal', SoalController::class);
});
Route::post('soal/upload-image', [SoalController::class, 'uploadImage'])->name('soal.upload-image');

// Fase 4: Proctor Monitoring
Route::middleware(['role:superadmin|operator|proktor|kepsek'])->prefix('monitoring')->name('monitoring.')->group(function () {
    Route::get('/', [ProctorController::class, 'monitoring'])->name('index');
    Route::get('/{jadwal}/status', [ProctorController::class, 'statusSiswa'])->name('status');
    Route::post('/durasi/{durasi}/reset', [ProctorController::class, 'resetLogin'])->name('reset');
    Route::post('/durasi/{durasi}/force-selesai', [ProctorController::class, 'forceSelesai'])->name('force-selesai');
});

// Fase 5: Grading (Koreksi Essai)
Route::middleware(['role:superadmin|guru'])->prefix('koreksi')->name('koreksi.')->group(function () {
    Route::get('/', [KoreksiController::class, 'index'])->name('index');
    Route::get('/{jadwal}/data', [KoreksiController::class, 'dataSiswa'])->name('data-siswa');
    Route::get('/{jadwal}/siswa/{siswa}', [KoreksiController::class, 'koreksiSiswa'])->name('koreksi-siswa');
    Route::post('/{jadwal}/siswa/{siswa}', [KoreksiController::class, 'simpanKoreksi'])->name('simpan-koreksi');
});

// Fase 6: Reporting & Document Generation
Route::middleware(['role:superadmin|operator|kepsek'])->prefix('report')->name('report.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/cetak-kartu', [ReportController::class, 'cetakKartu'])->name('cetak-kartu');
    Route::get('/cetak-daftar-hadir', [ReportController::class, 'cetakDaftarHadir'])->name('cetak-daftar-hadir');
    Route::get('/cetak-berita-acara', [ReportController::class, 'cetakBeritaAcara'])->name('cetak-berita-acara');
    Route::get('/rekap-nilai', [ReportController::class, 'rekapNilai'])->name('rekap-nilai');
});
