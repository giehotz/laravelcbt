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
        Schema::table('cbt_bank_soal', function (Blueprint $table) {
            $table->enum('skoring_kompleks', ['all_or_nothing', 'partial'])
                ->default('all_or_nothing')
                ->after('bobot_kompleks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cbt_bank_soal', function (Blueprint $table) {
            $table->dropColumn('skoring_kompleks');
        });
    }
};
