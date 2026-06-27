<?php

namespace App\Policies\Cbt;

use App\Models\Cbt\Jadwal;
use App\Models\User;

class JadwalPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasMinRoleLevel(60)) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasMinRoleLevel(40);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Jadwal $jadwal): bool
    {
        return $user->hasMinRoleLevel(40) && $jadwal->bankSoal?->guru_id === $user->guru?->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasMinRoleLevel(40);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Jadwal $jadwal): bool
    {
        return $user->hasMinRoleLevel(40) && $jadwal->bankSoal?->guru_id === $user->guru?->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Jadwal $jadwal): bool
    {
        return $user->hasMinRoleLevel(40) && $jadwal->bankSoal?->guru_id === $user->guru?->id;
    }

    /**
     * Determine whether the user can grade/correct essay questions for the schedule.
     */
    public function correctEssay(User $user, Jadwal $jadwal): bool
    {
        return $user->hasMinRoleLevel(40) && $jadwal->bankSoal?->guru_id === $user->guru?->id;
    }
}
