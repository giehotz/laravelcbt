<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cbt_soal_pairs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('soal_id')->constrained('cbt_soal')->cascadeOnDelete();
            $table->text('kiri');
            $table->text('kanan');
            $table->timestamps();

            $table->index('soal_id');
        });

        // Migrate existing matching questions (jenis = 3)
        $soals = DB::table('cbt_soal')->where('jenis', 3)->get();
        foreach ($soals as $soal) {
            if ($soal->jawaban) {
                $decoded = json_decode($soal->jawaban, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && count($decoded) >= 2) {
                    $headers = $decoded[0]; // ["#", "kanan1", "kanan2", ...]
                    for ($i = 1; $i < count($decoded); $i++) {
                        $row = $decoded[$i]; // ["kiri", "0", "1", ...]
                        if (is_array($row) && count($row) >= 2) {
                            $kiri = $row[0];
                            $matchIdx = array_search("1", array_slice($row, 1));
                            if ($matchIdx !== false && isset($headers[$matchIdx + 1])) {
                                $kanan = $headers[$matchIdx + 1];
                                DB::table('cbt_soal_pairs')->insert([
                                    'soal_id' => $soal->id,
                                    'kiri' => $kiri,
                                    'kanan' => $kanan,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_soal_pairs');
    }
};
