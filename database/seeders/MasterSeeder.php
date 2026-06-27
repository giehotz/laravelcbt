<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MasterSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. CBT Jenis
        DB::table('cbt_jenis')->updateOrInsert(
            ['id' => 1],
            ['nama_jenis' => 'Penilaian Harian', 'kode_jenis' => 'PH', 'created_at' => $now, 'updated_at' => $now]
        );

        // 2. Tahun Pelajaran
        DB::table('tahun_pelajaran')->updateOrInsert(
            ['id' => 1],
            ['tahun' => '2025/2026', 'active' => 1, 'created_at' => $now, 'updated_at' => $now]
        );

        // 3. Semesters
        DB::table('semesters')->updateOrInsert(
            ['id' => 2],
            ['smt' => 2, 'nama_smt' => 'Genap', 'active' => 1, 'created_at' => $now, 'updated_at' => $now]
        );

        // 4. Guru
        DB::table('guru')->updateOrInsert(
            ['id' => 1],
            [
                'user_id' => 3,
                'nama_guru' => 'Guru Fikih',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // 5. Level Kelas
        DB::table('level_kelas')->updateOrInsert(
            ['id' => 4],
            ['level' => '4', 'created_at' => $now, 'updated_at' => $now]
        );

        // 6. Jurusan
        DB::table('jurusan')->updateOrInsert(
            ['id' => 1],
            ['nama_jurusan' => 'Umum', 'kode_jurusan' => 'UM', 'created_at' => $now, 'updated_at' => $now]
        );

        // 7. Kelas
        DB::table('kelas')->updateOrInsert(
            ['id' => 2],
            [
                'tahun_pelajaran_id' => 1,
                'semester_id' => 2,
                'nama_kelas' => '4A',
                'kode_kelas' => '4A',
                'jurusan_id' => 1,
                'level_id' => 4,
                'guru_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
