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
        Schema::create('rapor_nilai_akhir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tp_id')->constrained('tahun_pelajaran');
            $table->foreignId('smt_id')->constrained('semesters');
            $table->foreignId('mapel_id')->nullable()->constrained('mapel');
            $table->foreignId('kelas_id')->nullable()->constrained('kelas');
            $table->foreignId('siswa_id')->nullable()->constrained('siswa');
            $table->decimal('nilai_ph', 5, 2)->default(0);
            $table->decimal('nilai_pts', 5, 2)->default(0);
            $table->decimal('nilai_pas', 5, 2)->default(0);
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->char('predikat', 1)->nullable();
            // Sumber: 'manual' atau 'cbt'
            $table->string('sumber_ph', 10)->default('manual');
            $table->string('sumber_pts', 10)->default('manual');
            $table->string('sumber_pas', 10)->default('manual');
            $table->boolean('final')->default(false); // sudah dikunci guru
            $table->timestamps();
            $table->unique(['tp_id', 'smt_id', 'mapel_id', 'siswa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapor_nilai_akhir');
    }
};
