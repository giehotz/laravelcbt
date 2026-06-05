<?php

namespace Database\Seeders;

use App\Models\Master\Mapel;
use Illuminate\Database\Seeder;

class MapelSeeder extends Seeder
{
    public function run(): void
    {
        $mapels = [
            // Kelompok A (Agama Islam)
            ['nama_mapel' => "Al-Qur'an Hadis", 'kode' => 'QH', 'kelompok' => 'A', 'urutan' => 1],
            ['nama_mapel' => "Akidah Akhlak", 'kode' => 'AA', 'kelompok' => 'A', 'urutan' => 2],
            ['nama_mapel' => "Fikih", 'kode' => 'FQ', 'kelompok' => 'A', 'urutan' => 3],
            ['nama_mapel' => "Sejarah Kebudayaan Islam (SKI)", 'kode' => 'SKI', 'kelompok' => 'A', 'urutan' => 4],
            
            // Kelompok B (Umum)
            ['nama_mapel' => "Pendidikan Pancasila", 'kode' => 'PPKN', 'kelompok' => 'B', 'urutan' => 5],
            ['nama_mapel' => "Bahasa Indonesia", 'kode' => 'BIND', 'kelompok' => 'B', 'urutan' => 6],
            ['nama_mapel' => "Bahasa Arab", 'kode' => 'BARB', 'kelompok' => 'B', 'urutan' => 7],
            ['nama_mapel' => "Matematika", 'kode' => 'MTK', 'kelompok' => 'B', 'urutan' => 8],
            ['nama_mapel' => "Ilmu Pengetahuan Alam dan Sosial (IPAS)", 'kode' => 'IPAS', 'kelompok' => 'B', 'urutan' => 9],
            ['nama_mapel' => "Pendidikan Jasmani, Olahraga, dan Kesehatan (PJOK)", 'kode' => 'PJOK', 'kelompok' => 'B', 'urutan' => 10],
            ['nama_mapel' => "Seni dan Prakarya (Seni Musik, Seni Rupa, Seni Teater, Seni Tari, atau Prakarya)", 'kode' => 'SBK', 'kelompok' => 'B', 'urutan' => 11],
            ['nama_mapel' => "Muatan Lokal (disesuaikan dengan kekhasan daerah atau madrasah)", 'kode' => 'MULOK', 'kelompok' => 'B', 'urutan' => 12],
        ];

        foreach ($mapels as $mapel) {
            Mapel::firstOrCreate(['nama_mapel' => $mapel['nama_mapel']], $mapel);
        }
    }
}
