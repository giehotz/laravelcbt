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
        Schema::create('cbt_soal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_id')->constrained('cbt_bank_soal')->cascadeOnDelete();
            $table->foreignId('mapel_id')->nullable()->constrained('mapel')->nullOnDelete();
            // Tipe: 1=PG, 2=Ganda Kompleks, 3=Menjodohkan, 4=Isian Singkat, 5=Uraian/Esai
            $table->tinyInteger('jenis');
            $table->integer('nomor_soal')->default(0);
            $table->string('file', 255)->nullable(); // Path media lampiran soal
            $table->text('tipe_file')->nullable();
            $table->longText('soal')->nullable(); // HTML dari rich editor

            // Pilihan jawaban PG
            $table->longText('opsi_a')->nullable();
            $table->longText('opsi_b')->nullable();
            $table->longText('opsi_c')->nullable();
            $table->longText('opsi_d')->nullable();
            $table->longText('opsi_e')->nullable();

            // File media per opsi
            $table->string('file_a', 255)->nullable();
            $table->string('file_b', 255)->nullable();
            $table->string('file_c', 255)->nullable();
            $table->string('file_d', 255)->nullable();
            $table->string('file_e', 255)->nullable();

            $table->longText('jawaban')->nullable(); // Kunci: "A", "B", atau JSON untuk kompleks
            $table->longText('deskripsi')->nullable(); // Pembahasan
            $table->tinyInteger('kesulitan')->default(1); // 1-10
            $table->boolean('timer')->default(false);
            $table->integer('timer_menit')->default(0);
            $table->boolean('tampilkan')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_soal');
    }
};
