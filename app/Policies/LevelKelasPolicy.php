<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Master\LevelKelas;

class LevelKelasPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator', 'guru']);
    }

    public function view(User $user, LevelKelas $levelKelas): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator', 'guru']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }

    public function update(User $user, LevelKelas $levelKelas): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }

    public function delete(User $user, LevelKelas $levelKelas): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }
}
