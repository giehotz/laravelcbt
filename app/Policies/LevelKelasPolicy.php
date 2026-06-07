<?php

namespace App\Policies;

use App\Models\Master\LevelKelas;
use App\Models\User;

class LevelKelasPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRoleLevel(40);
    }

    public function view(User $user, LevelKelas $levelKelas): bool
    {
        return $user->hasMinRoleLevel(40);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRoleLevel(60);
    }

    public function update(User $user, LevelKelas $levelKelas): bool
    {
        return $user->hasMinRoleLevel(60);
    }

    public function delete(User $user, LevelKelas $levelKelas): bool
    {
        return $user->hasMinRoleLevel(60);
    }
}
