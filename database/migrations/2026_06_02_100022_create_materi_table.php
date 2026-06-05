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
        Schema::create('materi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran');
            $table->foreignId('semester_id')->constrained('semesters');
            $table->foreignId('guru_id')->nullable()->constrained('guru')->nullOnDelete();
            $table->foreignId('mapel_id')->nullable()->constrained('mapel')->nullOnDelete();
            $table->json('kelas'); // Array id kelas tujuan
            $table->string('judul', 500);
            $table->longText('isi');
            $table->json('file')->nullable(); // JSON array path file
            $table->string('link_file', 255)->nullable();
            $table->string('youtube', 255)->nullable();
            $table->dateTime('tgl_mulai')->nullable();
            $table->tinyInteger('status')->default(0); // 0=draft, 1=publish
            $table->tinyInteger('jenis')->default(1); // 1=materi, 2=tugas
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materi');
    }
};
