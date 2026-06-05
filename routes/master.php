<?php

use App\Http\Controllers\Master\TahunPelajaranController;
use App\Http\Controllers\Master\SemesterController;
use Illuminate\Support\Facades\Route;

Route::get('tahun-pelajaran', [TahunPelajaranController::class, 'index'])->name('tahun-pelajaran.index');
Route::post('tahun-pelajaran', [TahunPelajaranController::class, 'store'])->name('tahun-pelajaran.store');
Route::put('tahun-pelajaran/{tahun_pelajaran}', [TahunPelajaranController::class, 'update'])->name('tahun-pelajaran.update');
Route::post('tahun-pelajaran/{tahun_pelajaran}/activate', [TahunPelajaranController::class, 'activate'])->name('tahun-pelajaran.activate');
Route::delete('tahun-pelajaran/{tahun_pelajaran}', [TahunPelajaranController::class, 'destroy'])->name('tahun-pelajaran.destroy');

Route::get('semester', [SemesterController::class, 'index'])->name('semester.index');
Route::post('semester', [SemesterController::class, 'store'])->name('semester.store');
Route::put('semester/{semester}', [SemesterController::class, 'update'])->name('semester.update');
Route::post('semester/{semester}/activate', [SemesterController::class, 'activate'])->name('semester.activate');
Route::delete('semester/{semester}', [SemesterController::class, 'destroy'])->name('semester.destroy');

use App\Http\Controllers\Master\LevelKelasController;
use App\Http\Controllers\Master\JurusanController;
use App\Http\Controllers\Master\MapelController;
use App\Http\Controllers\Master\EkstraController;
use App\Http\Controllers\Master\KelasController;
use App\Http\Controllers\Master\BukuIndukController;
Route::post('level-kelas/generate', [LevelKelasController::class, 'generate'])->name('level-kelas.generate');
Route::resource('level-kelas', LevelKelasController::class)
    ->parameters(['level-kelas' => 'levelKelas'])
    ->except(['create', 'show', 'edit']);
Route::resource('jurusan', JurusanController::class)->except(['create', 'show', 'edit']);
Route::resource('mapel', MapelController::class)->except(['create', 'show', 'edit']);
Route::resource('ekstra', EkstraController::class)->except(['create', 'show', 'edit']);
Route::resource('kelas', KelasController::class)
    ->parameters(['kelas' => 'kelas'])
    ->except(['create', 'show', 'edit']);

Route::get('kelas/{kelas}/students', [KelasController::class, 'editStudents'])->name('kelas.students.edit');
Route::put('kelas/{kelas}/students', [KelasController::class, 'updateStudents'])->name('kelas.students.update');

Route::resource('buku-induk', BukuIndukController::class)
    ->parameters(['buku-induk' => 'buku_induk'])
    ->except(['create', 'show', 'store', 'destroy']);
