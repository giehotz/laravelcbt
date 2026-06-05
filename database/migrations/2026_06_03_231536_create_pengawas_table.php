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
        Schema::create('cbt_pengawas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('cbt_jadwal')->cascadeOnDelete();
            $table->foreignId('ruang_id')->constrained('cbt_ruang')->cascadeOnDelete();
            $table->foreignId('sesi_id')->constrained('cbt_sesi')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->timestamps();

            // Sesuai konfirmasi: 1 Pengawas per Ruang per Sesi per Jadwal.
            // Kita lock di (jadwal, ruang, sesi).
            $table->unique(['jadwal_id', 'ruang_id', 'sesi_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_pengawas');
    }
};
