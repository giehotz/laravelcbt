<?php

namespace App\Services;

use App\Models\Master\Guru;
use App\Models\Master\GuruMapelKelas;
use Illuminate\Support\Collection;

class GuruAssignmentService
{
    protected AcademicPeriodService $periodService;

    public function __construct(AcademicPeriodService $periodService)
    {
        $this->periodService = $periodService;
    }

    /**
     * Get all Mapel assigned to a Guru for a specific period.
     * Optionally defaults to the active period.
     */
    public function allowedMapels(Guru $guru, ?int $tpId = null, ?int $smtId = null): Collection
    {
        $tpId = $this->periodService->resolveTpId($tpId);
        $smtId = $this->periodService->resolveSmtId($smtId);

        return GuruMapelKelas::with('mapel')
            ->where('guru_id', $guru->id)
            ->where('tahun_pelajaran_id', $tpId)
            ->where('semester_id', $smtId)
            ->get()
            ->pluck('mapel')
            ->unique('id')
            ->values();
    }

    /**
     * Get all Kelas assigned to a Guru for a specific Mapel and period.
     */
    public function allowedClasses(Guru $guru, int $mapelId, ?int $tpId = null, ?int $smtId = null): Collection
    {
        $tpId = $this->periodService->resolveTpId($tpId);
        $smtId = $this->periodService->resolveSmtId($smtId);

        return GuruMapelKelas::with('kelas')
            ->where('guru_id', $guru->id)
            ->where('mapel_id', $mapelId)
            ->where('tahun_pelajaran_id', $tpId)
            ->where('semester_id', $smtId)
            ->get()
            ->pluck('kelas')
            ->unique('id')
            ->values();
    }

    /**
     * Check if a Guru is allowed to teach a specific Mapel in specific Classes.
     */
    public function isAllowed(Guru $guru, int $mapelId, array $kelasIds, ?int $tpId = null, ?int $smtId = null): bool
    {
        $tpId = $this->periodService->resolveTpId($tpId);
        $smtId = $this->periodService->resolveSmtId($smtId);

        $allowedKelasIds = GuruMapelKelas::where('guru_id', $guru->id)
            ->where('mapel_id', $mapelId)
            ->where('tahun_pelajaran_id', $tpId)
            ->where('semester_id', $smtId)
            ->whereIn('kelas_id', $kelasIds)
            ->pluck('kelas_id')
            ->toArray();

        // If the number of found valid class IDs matches the requested count, it's allowed.
        return count($allowedKelasIds) === count(array_unique($kelasIds));
    }
}
