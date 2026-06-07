<?php

namespace App\Services;

use App\Models\Akademik\RaporConfig;
use App\Models\Akademik\RaporKkm;
use App\Models\Akademik\RaporNilaiAkhir;
use App\Models\Master\Mapel;
use App\Models\Master\Semester;
use App\Models\Master\Siswa;
use App\Models\Master\TahunPelajaran;
use Illuminate\Support\Facades\DB;

class RaporService
{
    /**
     * Import nilai dari CBT untuk satu siswa + mapel + komponen
     */
    public function importDariCbt(
        Siswa $siswa,
        Mapel $mapel,
        TahunPelajaran $tp,
        Semester $smt,
        string $komponen  // 'ph' | 'pts' | 'pas'
    ): float {
        $config = RaporConfig::where('tp_id', $tp->id)
            ->where('smt_id', $smt->id)
            ->first();

        $jenisId = match ($komponen) {
            'ph' => $config?->jenis_ph_id,
            'pts' => $config?->jenis_pts_id,
            'pas' => $config?->jenis_pas_id,
            default => null,
        };

        if (! $jenisId) {
            return 0;
        }

        // Rata-rata semua ujian jenis ini untuk siswa + mapel di TP/SMT ini
        // Note: cbt_nilai depends on cbt_jadwal -> bank_soal -> mapel
        return (float) DB::table('cbt_nilai')
            ->join('cbt_jadwal', 'cbt_nilai.jadwal_id', '=', 'cbt_jadwal.id')
            ->join('cbt_bank_soal', 'cbt_jadwal.bank_id', '=', 'cbt_bank_soal.id')
            ->where('cbt_nilai.siswa_id', $siswa->id)
            ->where('cbt_bank_soal.mapel_id', $mapel->id)
            ->where('cbt_jadwal.jenis_id', $jenisId)
            ->where('cbt_jadwal.tahun_pelajaran_id', $tp->id)
            ->where('cbt_jadwal.semester_id', $smt->id)
            ->avg(DB::raw('pg_nilai + esai_nilai + kompleks_nilai + jodohkan_nilai + isian_nilai'));
    }

    /**
     * Hitung nilai akhir berdasarkan bobot dari rapor_kkm
     */
    public function hitungNilaiAkhir(RaporNilaiAkhir $nilai, RaporKkm $kkm): float
    {
        return round(
            ($nilai->nilai_ph * $kkm->bobot_ph / 100) +
            ($nilai->nilai_pts * $kkm->bobot_pts / 100) +
            ($nilai->nilai_pas * $kkm->bobot_pas / 100),
            2
        );
    }

    /**
     * Tentukan predikat berdasarkan KKM
     */
    public function predikat(float $nilai, int $kkm): string
    {
        return match (true) {
            $nilai >= 90 => 'A',
            $nilai >= $kkm + 10 => 'B',
            $nilai >= $kkm => 'C',
            default => 'D',
        };
    }
}
