<?php

namespace App\Services;

use App\Models\Semester;
use App\Models\TahunPelajaran;

class AcademicPeriodService
{
    /**
     * Get the ID of the currently active TahunPelajaran.
     */
    public function activeTpId(): ?int
    {
        return TahunPelajaran::where('active', true)->value('id');
    }

    /**
     * Get the ID of the currently active Semester.
     */
    public function activeSmtId(): ?int
    {
        return Semester::where('active', true)->value('id');
    }

    /**
     * Resolve TP ID, defaulting to the active one if null.
     */
    public function resolveTpId(?int $tpId = null): ?int
    {
        return $tpId ?? $this->activeTpId();
    }

    /**
     * Resolve SMT ID, defaulting to the active one if null.
     */
    public function resolveSmtId(?int $smtId = null): ?int
    {
        return $smtId ?? $this->activeSmtId();
    }
}
