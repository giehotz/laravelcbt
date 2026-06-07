<?php

namespace App\Jobs\Cbt;

use App\Models\Cbt\KelasRuang;
use App\Models\Cbt\SesiSiswa;
use App\Models\Master\KelasSiswa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SyncSesiSiswaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public KelasRuang $kelasRuang
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function () {
            $tpId = $this->kelasRuang->tp_id;
            $smtId = $this->kelasRuang->smt_id;
            $kelasId = $this->kelasRuang->kelas_id;

            // 1. Dapatkan daftar siswa aktif di kelas tersebut pada TP & SMT ini
            $siswaIds = KelasSiswa::where('kelas_id', $kelasId)
                ->where('tahun_pelajaran_id', $tpId)
                ->where('semester_id', $smtId)
                ->pluck('siswa_id')
                ->toArray();

            if (empty($siswaIds)) {
                // Hapus semua yang bukan manual jika kelas kosong
                SesiSiswa::where('kelas_id', $kelasId)
                    ->where('tp_id', $tpId)
                    ->where('smt_id', $smtId)
                    ->where('is_manual', false)
                    ->delete();

                return;
            }

            // 2. Ambil siswa yang memiliki is_manual = true (mereka tidak boleh di-overwrite)
            $manualSiswaIds = SesiSiswa::where('kelas_id', $kelasId)
                ->where('tp_id', $tpId)
                ->where('smt_id', $smtId)
                ->where('is_manual', true)
                ->pluck('siswa_id')
                ->toArray();

            // 3. Siswa yang akan kita assign adalah siswa kelas INI dikurangi siswa yang is_manual
            $siswaToAssign = array_diff($siswaIds, $manualSiswaIds);

            // 4. Hapus record sesi_siswa kelas ini yang BUKAN manual,
            // untuk membersihkan sisa siswa yang mungkin sudah keluar dari kelas.
            SesiSiswa::where('kelas_id', $kelasId)
                ->where('tp_id', $tpId)
                ->where('smt_id', $smtId)
                ->where('is_manual', false)
                ->delete();

            // 5. Insert ulang record untuk siswaToAssign
            $inserts = [];
            foreach ($siswaToAssign as $siswaId) {
                $inserts[] = [
                    'siswa_id' => $siswaId,
                    'kelas_id' => $kelasId,
                    'ruang_id' => $this->kelasRuang->ruang_id,
                    'sesi_id' => $this->kelasRuang->sesi_id,
                    'tp_id' => $tpId,
                    'smt_id' => $smtId,
                    'is_manual' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            foreach (array_chunk($inserts, 500) as $chunk) {
                SesiSiswa::insert($chunk);
            }
        });
    }
}
