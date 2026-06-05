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
        Schema::create('mapel', function (Blueprint $table) {
            $table->id();
            $table->string('nama_mapel', 100);
            $table->string('kode', 20)->nullable();
            $table->string('kelompok', 10)->default('-');
            $table->integer('bobot_p')->default(0);
            $table->integer('bobot_k')->default(0);
            $table->integer('jenjang')->default(0);
            $table->integer('urutan')->default(0);
            $table->integer('urutan_tampil')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('deletable')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapel');
    }
};
