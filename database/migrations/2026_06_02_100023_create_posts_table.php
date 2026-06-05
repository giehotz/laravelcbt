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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dari_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('dari_group')->nullable(); // ID role group
            $table->json('kepada'); // e.g., {"type": "all"}, {"type": "kelas", "ids": [1, 2]}
            $table->longText('text');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
