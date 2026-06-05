<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cbt_rekap_nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->nullable()->constrained('cbt_jadwal')->nullOnDelete();
            $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran');
            $table->foreignId('semester_id')->constrained('semesters');
            $table->foreignId('jenis_id')->constrained('cbt_jenis');
            $table->foreignId('bank_id')->nullable()->constrained('cbt_bank_soal')->nullOnDelete();
            $table->foreignId('mapel_id')->nullable()->constrained('mapel')->nullOnDelete();
            $table->foreignId('siswa_id')->nullable()->constrained('siswa')->nullOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->string('kelas', 30); // Nama kelas historis (denormalisasi)
            $table->string('mulai', 25);
            $table->string('selesai', 25);
            $table->string('durasi', 25);
            $table->integer('bobot_pg');
            $table->longText('jawaban_pg');
            $table->string('nilai_pg', 10);
            $table->integer('bobot_esai');
            $table->longText('jawaban_esai');
            $table->string('nilai_esai', 10);
            $table->foreignId('guru_id')->nullable()->constrained('guru')->nullOnDelete();
            $table->string('nama_siswa', 150)->nullable();
            $table->string('no_peserta', 50)->nullable();
            $table->json('soal_kompleks')->nullable();
            $table->json('soal_jodohkan')->nullable();
            $table->json('soal_isian')->nullable();
            $table->json('soal_essai')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_rekap_nilai');
    }
};
