<?php

namespace App\Services;

use App\Models\Master\Kelas;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KelasStudentService
{
    /**
     * Synchronize class roster for a specific academic year and semester.
     * Throws ValidationException if any student is already enrolled in a different class in the same period.
     *
     * @throws ValidationException
     */
    public function syncStudents(Kelas $kelas, array $siswaIds, int $tahunPelajaranId, int $semesterId): void
    {
        // 1. Business Check: Ensure no selected student is already assigned to a different class in this active period
        $duplicates = DB::table('kelas_siswa')
            ->join('siswa', 'kelas_siswa.siswa_id', '=', 'siswa.id')
            ->join('kelas', 'kelas_siswa.kelas_id', '=', 'kelas.id')
            ->where('kelas_siswa.tahun_pelajaran_id', $tahunPelajaranId)
            ->where('kelas_siswa.semester_id', $semesterId)
            ->where('kelas_siswa.kelas_id', '!=', $kelas->id)
            ->whereIn('kelas_siswa.siswa_id', $siswaIds)
            ->select('siswa.nama as nama_siswa', 'kelas.nama_kelas')
            ->get();

        if ($duplicates->isNotEmpty()) {
            $messages = [];
            foreach ($duplicates as $dup) {
                $messages[] = "Siswa {$dup->nama_siswa} sudah terdaftar di kelas {$dup->nama_kelas} untuk periode ini.";
            }
            throw ValidationException::withMessages(['siswa_ids' => $messages]);
        }

        // 2. Scope-based sync execution using attach/detach to preserve other periods
        DB::transaction(function () use ($kelas, $siswaIds, $tahunPelajaranId, $semesterId) {
            $existingIds = $kelas->siswa()
                ->wherePivot('tahun_pelajaran_id', $tahunPelajaranId)
                ->wherePivot('semester_id', $semesterId)
                ->pluck('siswa.id')
                ->toArray();

            $toAdd = array_diff($siswaIds, $existingIds);
            $toRemove = array_diff($existingIds, $siswaIds);

            // Attach additions
            foreach ($toAdd as $siswaId) {
                $kelas->siswa()->attach($siswaId, [
                    'tahun_pelajaran_id' => $tahunPelajaranId,
                    'semester_id' => $semesterId,
                ]);
            }

            // Detach removals
            if (! empty($toRemove)) {
                $kelas->siswa()
                    ->wherePivot('tahun_pelajaran_id', $tahunPelajaranId)
                    ->wherePivot('semester_id', $semesterId)
                    ->detach($toRemove);
            }
        });
    }
}
