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
        Schema::create('cbt_kop_berita', function (Blueprint $table) {
            $table->id();
            $table->string('header_1', 150)->nullable();
            $table->string('header_2', 150)->nullable();
            $table->string('header_3', 150)->nullable();
            $table->string('header_4', 150)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_kop_berita');
    }
};
