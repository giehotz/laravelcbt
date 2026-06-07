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
        Schema::create('cbt_soal_siswa', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('bank_id')->constrained('cbt_bank_soal');
            $table->foreignId('jadwal_id')->constrained('cbt_jadwal');
            $table->foreignId('soal_id')->constrained('cbt_soal');
            $table->foreignId('siswa_id')->constrained('siswa');
            $table->tinyInteger('jenis_soal');
            $table->integer('no_soal_alias'); // Nomor tampil ke siswa

            // Alias acak opsi (mapping A->C, B->A, dst)
            $table->char('opsi_alias_a', 1)->nullable();
            $table->char('opsi_alias_b', 1)->nullable();
            $table->char('opsi_alias_c', 1)->nullable();
            $table->char('opsi_alias_d', 1)->nullable();
            $table->char('opsi_alias_e', 1)->nullable();

            $table->longText('jawaban_alias')->nullable(); // Kunci setelah alias
            $table->longText('jawaban_siswa')->nullable(); // Jawaban yang dipilih
            $table->longText('jawaban_benar')->nullable(); // Cache kunci benar
            $table->integer('point_esai')->default(0);
            $table->boolean('soal_end')->default(false); // Soal terakhir?
            $table->string('point_soal', 5)->default('0');
            $table->string('nilai_koreksi', 5)->default('0');
            $table->boolean('nilai_otomatis')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_soal_siswa');
    }
};
