<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Master\Kelas;

class KelasPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator', 'guru']);
    }

    public function view(User $user, Kelas $kelas): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator', 'guru']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }

    public function update(User $user, Kelas $kelas): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }

    public function delete(User $user, Kelas $kelas): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }

    /**
     * Determine whether the user can manage students in the class.
     * Allowed for superadmin, operator, or the assigned wali kelas.
     */
    public function manageStudents(User $user, Kelas $kelas): bool
    {
        if ($user->hasAnyRole(['superadmin', 'operator'])) {
            return true;
        }

        if ($user->hasRole('guru') && $user->guru && $user->guru->id === $kelas->guru_id) {
            return true;
        }

        return false;
    }
}
