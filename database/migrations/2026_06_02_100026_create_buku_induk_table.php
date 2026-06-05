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
        Schema::create('buku_induk', function (Blueprint $table) {
            $table->foreignId('siswa_id')->primary()->constrained('siswa')->cascadeOnDelete();
            $table->string('uid', 100)->nullable();
            $table->string('nama_panggilan', 80)->nullable();
            $table->string('bahasa', 80)->nullable();
            $table->integer('jml_saudara_kandung')->default(0);
            $table->integer('jml_saudara_tiri')->default(0);
            $table->integer('jml_saudara_angkat')->default(0);
            $table->tinyInteger('yatim')->default(0); // 0=ada orang-tua, 1=yatim, 2=yatim piatu
            $table->char('tinggal_bersama', 1)->default('1'); // 1=orang-tua, 2=saudara, 3=wali, 4=asrama, 5=kost, 6=lainnya
            $table->string('jarak', 15)->nullable();
            $table->string('gol_darah', 5)->nullable();
            $table->text('penyakit')->nullable();
            $table->string('kelainan_fisik', 150)->nullable();
            $table->text('kegemaran')->nullable();
            $table->text('beasiswa')->nullable();
            $table->string('no_ijazah_sebelumnya', 80)->nullable();
            $table->string('tahun_lulus_sebelumnya', 10)->nullable();
            $table->string('pindahan_dari', 150)->nullable();
            $table->string('alasan_kepindahan', 300)->nullable();
            $table->tinyInteger('status')->default(1); // 1=aktif, 2=lulus, 3=pindah, 4=keluar
            $table->integer('tahun_lulus')->nullable();
            $table->string('no_ijazah', 80)->nullable();
            $table->string('kelas_akhir', 80)->nullable();
            $table->text('catatan_penting')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buku_induk');
    }
};
