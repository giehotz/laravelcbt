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
        Schema::create('cbt_durasi_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa');
            $table->foreignId('jadwal_id')->constrained('cbt_jadwal');
            $table->tinyInteger('status')->default(0); // 0=belum, 1=sedang, 2=selesai
            $table->time('lama_ujian')->nullable();
            $table->string('mulai', 25)->nullable();
            $table->string('selesai', 25)->nullable();
            $table->tinyInteger('reset')->default(0); // 0=tidak, 1=reset 0, 2=reset sisa, 3=ulang semua
            $table->timestamps();

            $table->unique(['siswa_id', 'jadwal_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_durasi_siswa');
    }
};
