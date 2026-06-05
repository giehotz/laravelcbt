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
        Schema::create('cbt_sesi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sesi', 80);
            $table->string('kode_sesi', 15)->unique();
            $table->time('waktu_mulai');
            $table->time('waktu_akhir');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_sesi');
    }
};
