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
        Schema::create('rapor_kkm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tp_id')->constrained('tahun_pelajaran');
            $table->foreignId('smt_id')->constrained('semesters');
            $table->foreignId('kelas_id')->nullable()->constrained('kelas');
            $table->foreignId('mapel_id')->nullable()->constrained('mapel');
            $table->integer('kkm')->default(0);
            $table->integer('bobot_ph')->default(40);   // % bobot PH
            $table->integer('bobot_pts')->default(30);  // % bobot PTS
            $table->integer('bobot_pas')->default(30);  // % bobot PAS
            $table->timestamps();
            $table->unique(['tp_id','smt_id','kelas_id','mapel_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapor_kkm');
    }
};
