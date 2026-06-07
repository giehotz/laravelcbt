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
        Schema::create('cbt_bank_soal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_id')->constrained('cbt_jenis');
            $table->string('kode', 50)->default('0');
            $table->string('level', 50);
            $table->json('kelas'); // Array id kelas yang dapat akses
            $table->foreignId('mapel_id')->nullable()->constrained('mapel')->nullOnDelete();
            $table->foreignId('jurusan_id')->nullable()->constrained('jurusan')->nullOnDelete();
            $table->foreignId('guru_id')->nullable()->constrained('guru')->nullOnDelete();
            $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran');
            $table->foreignId('semester_id')->constrained('semesters');
            $table->string('nama', 255);
            $table->integer('kkm')->default(0);

            // Jumlah soal per tipe
            $table->integer('jml_pg')->default(0);
            $table->integer('tampil_pg')->default(0);
            $table->integer('bobot_pg')->default(0);
            $table->integer('jml_esai')->default(0);
            $table->integer('tampil_esai')->default(0);
            $table->integer('bobot_esai')->default(0);
            $table->integer('jml_kompleks')->default(0);
            $table->integer('tampil_kompleks')->default(0);
            $table->integer('bobot_kompleks')->default(0);
            $table->integer('jml_jodohkan')->default(0);
            $table->integer('tampil_jodohkan')->default(0);
            $table->integer('bobot_jodohkan')->default(0);
            $table->integer('jml_isian')->default(0);
            $table->integer('tampil_isian')->default(0);
            $table->integer('bobot_isian')->default(0);

            $table->integer('opsi')->default(4); // 3=A-C, 4=A-D, 5=A-E
            $table->text('deskripsi')->nullable();
            $table->tinyInteger('status')->default(0); // 0=draft, 1=aktif
            $table->tinyInteger('status_soal')->default(0); // 0=belum selesai, 1=sudah selesai
            $table->string('soal_agama', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_bank_soal');
    }
};
