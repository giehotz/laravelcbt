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
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->string('nisn', 12)->unique();
            $table->string('nis', 20)->unique();
            $table->string('nama', 100);
            $table->char('jenis_kelamin', 1)->nullable(); // L / P
            $table->unsignedBigInteger('kelas_awal')->nullable();
            $table->string('tahun_masuk', 10)->nullable();
            $table->string('sekolah_asal', 100)->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama', 15)->nullable();
            $table->string('hp', 15)->nullable();
            $table->string('email', 254)->nullable();
            $table->string('foto', 255)->default('siswa.png');
            $table->integer('anak_ke')->nullable();
            $table->char('status_keluarga', 1)->nullable();
            $table->text('alamat')->nullable();
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('kelurahan', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kabupaten', 100)->nullable();
            $table->string('provinsi', 100)->nullable();
            $table->integer('kode_pos')->nullable();
            // Data Orang Tua - Ayah
            $table->string('nama_ayah', 150)->nullable();
            $table->string('tgl_lahir_ayah', 50)->nullable();
            $table->string('pendidikan_ayah', 50)->nullable();
            $table->string('pekerjaan_ayah', 100)->nullable();
            $table->string('nohp_ayah', 20)->nullable();
            $table->text('alamat_ayah')->nullable();
            // Data Orang Tua - Ibu
            $table->string('nama_ibu', 100)->nullable();
            $table->string('tgl_lahir_ibu', 50)->nullable();
            $table->string('pendidikan_ibu', 50)->nullable();
            $table->string('pekerjaan_ibu', 100)->nullable();
            $table->string('nohp_ibu', 20)->nullable();
            $table->text('alamat_ibu')->nullable();
            // Data Wali
            $table->string('nama_wali', 150)->nullable();
            $table->string('tgl_lahir_wali', 50)->nullable();
            $table->string('pendidikan_wali', 50)->nullable();
            $table->string('pekerjaan_wali', 100)->nullable();
            $table->string('nohp_wali', 20)->nullable();
            $table->text('alamat_wali')->nullable();
            
            $table->string('nik', 30)->nullable();
            $table->string('warga_negara', 20)->default('WNI');
            $table->string('uid', 255)->nullable()->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
