<?php

namespace App\Actions\Cbt;

use App\Models\Cbt\NomorPeserta;
use App\Models\Master\KelasSiswa;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use Exception;
use Illuminate\Support\Facades\DB;

class GenerateNomorPesertaAction
{
    public function execute(TahunPelajaran $tp, Semester $smt): void
    {
        DB::transaction(function () use ($tp, $smt) {
            // 1. Cek tidak ada jadwal aktif/sedang berjalan di TP+SMT ini
            $ujianAktif = DB::table('cbt_jadwal')
                ->where('tahun_pelajaran_id', $tp->id)
                ->where('semester_id', $smt->id)
                ->where('status', 1)
                ->exists();

            if ($ujianAktif) {
                throw new Exception('Tidak bisa generate nomor peserta saat ujian sedang berlangsung.');
            }

            // 2. Hapus semua nomor lama untuk TP+SMT ini
            NomorPeserta::where('tp_id', $tp->id)
                ->where('smt_id', $smt->id)
                ->delete();

            // 3. Ambil siswa berurut berdasarkan nama kelas lalu nama siswa
            $kelasSiswaList = KelasSiswa::with(['siswa', 'kelas'])
                ->where('kelas_siswa.tahun_pelajaran_id', $tp->id)
                ->where('kelas_siswa.semester_id', $smt->id)
                ->join('kelas', 'kelas_siswa.kelas_id', '=', 'kelas.id')
                ->join('siswa', 'kelas_siswa.siswa_id', '=', 'siswa.id')
                ->orderBy('kelas.nama_kelas')
                ->orderBy('siswa.nama')
                ->select('kelas_siswa.*')
                ->get();

            // 4. Generate baru dalam satu batch insert
            $nomorBase = 1;
            $inserts = [];

            foreach ($kelasSiswaList as $ks) {
                $inserts[] = [
                    'siswa_id' => $ks->siswa_id,
                    'tp_id' => $tp->id,
                    'smt_id' => $smt->id,
                    'nomor_peserta' => str_pad($nomorBase++, 4, '0', STR_PAD_LEFT),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Chunk insert
            foreach (array_chunk($inserts, 500) as $chunk) {
                NomorPeserta::insert($chunk);
            }
        });
    }
}
