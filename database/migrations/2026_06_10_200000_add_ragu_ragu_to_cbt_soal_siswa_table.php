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
            // Add column only if it does not exist
            if (! Schema::hasColumn('cbt_soal_siswa', 'ragu_ragu')) {
                $table->boolean('ragu_ragu')->default(false)->after('jawaban_siswa');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cbt_soal_siswa', function (Blueprint $table) {
            if (Schema::hasColumn('cbt_soal_siswa', 'ragu_ragu')) {
                $table->dropColumn('ragu_ragu');
            }
        });
    }
};
