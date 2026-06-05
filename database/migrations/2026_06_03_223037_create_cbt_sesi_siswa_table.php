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
        Schema::create('cbt_sesi_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('ruang_id')->constrained('cbt_ruang')->cascadeOnDelete();
            $table->foreignId('sesi_id')->constrained('cbt_sesi')->cascadeOnDelete();
            $table->foreignId('tp_id')->constrained('tahun_pelajaran')->cascadeOnDelete();
            $table->foreignId('smt_id')->constrained('semesters')->cascadeOnDelete();
            $table->boolean('is_manual')->default(false);
            $table->timestamps();

            $table->unique(['siswa_id', 'tp_id', 'smt_id'], 'sesi_siswa_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_sesi_siswa');
    }
};
