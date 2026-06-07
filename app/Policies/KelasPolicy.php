<?php

namespace App\Policies;

use App\Models\Master\Kelas;
use App\Models\User;

class KelasPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRoleLevel(40);
    }

    public function view(User $user, Kelas $kelas): bool
    {
        return $user->hasMinRoleLevel(40);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRoleLevel(60);
    }

    public function update(User $user, Kelas $kelas): bool
    {
        return $user->hasMinRoleLevel(60);
    }

    public function delete(User $user, Kelas $kelas): bool
    {
        return $user->hasMinRoleLevel(60);
    }

    /**
     * Determine whether the user can manage students in the class.
     * Allowed for superadmin, operator, or the assigned wali kelas.
     */
    public function manageStudents(User $user, Kelas $kelas): bool
    {
        if ($user->hasMinRoleLevel(60)) {
            return true;
        }

        if ($user->hasMinRoleLevel(40) && $user->guru && $user->guru->id === $kelas->guru_id) {
            return true;
        }

        return false;
    }
}
