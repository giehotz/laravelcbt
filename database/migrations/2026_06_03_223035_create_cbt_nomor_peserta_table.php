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
        Schema::create('cbt_nomor_peserta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('tp_id')->constrained('tahun_pelajaran')->cascadeOnDelete();
            $table->foreignId('smt_id')->constrained('semesters')->cascadeOnDelete();
            $table->string('nomor_peserta', 50);
            $table->timestamps();

            $table->unique(['nomor_peserta', 'tp_id', 'smt_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_nomor_peserta');
    }
};
