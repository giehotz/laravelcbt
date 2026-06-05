<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Master\Mapel;

class MapelPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator', 'guru']);
    }

    public function view(User $user, Mapel $mapel): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator', 'guru']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }

    public function update(User $user, Mapel $mapel): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }

    public function delete(User $user, Mapel $mapel): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }
}
