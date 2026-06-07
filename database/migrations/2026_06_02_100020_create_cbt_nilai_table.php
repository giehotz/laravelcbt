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
        Schema::create('cbt_nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa');
            $table->foreignId('jadwal_id')->constrained('cbt_jadwal');
            $table->integer('pg_benar')->default(0);
            $table->string('pg_nilai', 10)->default('0');
            $table->string('esai_nilai', 10)->default('0');
            $table->string('kompleks_nilai', 10)->default('0');
            $table->string('jodohkan_nilai', 10)->default('0');
            $table->string('isian_nilai', 10)->default('0');
            $table->boolean('dikoreksi')->default(false);
            $table->timestamps();

            $table->unique(['siswa_id', 'jadwal_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_nilai');
    }
};
