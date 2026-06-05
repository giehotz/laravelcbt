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
        Schema::create('cbt_jadwal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran');
            $table->foreignId('semester_id')->constrained('semesters');
            $table->foreignId('bank_id')->nullable()->constrained('cbt_bank_soal')->nullOnDelete();
            $table->foreignId('jenis_id')->nullable()->constrained('cbt_jenis')->nullOnDelete();
            $table->string('tgl_mulai', 25);
            $table->string('tgl_selesai', 25);
            $table->integer('durasi_ujian'); // Menit
            $table->json('pengawas')->nullable(); // Array id guru
            $table->boolean('acak_soal')->default(false);
            $table->boolean('acak_opsi')->default(false);
            $table->boolean('hasil_tampil')->default(false); // Tampil hasil langsung ke siswa
            $table->boolean('token')->default(false); // Pakai token?
            $table->tinyInteger('status')->default(0); // 0=tutup, 1=buka
            $table->boolean('ulang')->default(false); // Boleh mengulangi ujian?
            $table->boolean('reset_login')->default(false);
            $table->boolean('rekap')->default(false);
            $table->integer('jam_ke')->default(0);
            $table->integer('jarak')->default(0); // Menit jarak antar sesi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_jadwal');
    }
};
