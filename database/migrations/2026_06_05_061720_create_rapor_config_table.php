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
        Schema::create('rapor_config', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tp_id')->constrained('tahun_pelajaran');
            $table->foreignId('smt_id')->constrained('semesters');
            // Mapping: id dari cbt_jenis ke komponen
            $table->foreignId('jenis_ph_id')->nullable()->constrained('cbt_jenis');
            $table->foreignId('jenis_pts_id')->nullable()->constrained('cbt_jenis');
            $table->foreignId('jenis_pas_id')->nullable()->constrained('cbt_jenis');
            $table->timestamps();
            $table->unique(['tp_id','smt_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapor_config');
    }
};
