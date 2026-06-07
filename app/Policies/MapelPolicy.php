<?php

namespace App\Policies;

use App\Models\Master\Mapel;
use App\Models\User;

class MapelPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRoleLevel(40);
    }

    public function view(User $user, Mapel $mapel): bool
    {
        return $user->hasMinRoleLevel(40);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRoleLevel(60);
    }

    public function update(User $user, Mapel $mapel): bool
    {
        return $user->hasMinRoleLevel(60);
    }

    public function delete(User $user, Mapel $mapel): bool
    {
        return $user->hasMinRoleLevel(60);
    }
}
