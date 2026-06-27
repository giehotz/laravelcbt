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
        Schema::table('cbt_soal_siswa', function (Blueprint $table) {
            $table->boolean('ragu_ragu')->default(false)->after('jawaban_siswa');
            $table->unique(['siswa_id', 'jadwal_id', 'soal_id'], 'soal_siswa_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cbt_soal_siswa', function (Blueprint $table) {
            $table->dropColumn('ragu_ragu');
            $table->dropUnique('soal_siswa_unique');
        });
    }
};
