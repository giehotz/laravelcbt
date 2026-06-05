<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Master\Ekstra;

class EkstraPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator', 'guru']);
    }

    public function view(User $user, Ekstra $ekstra): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator', 'guru']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }

    public function update(User $user, Ekstra $ekstra): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }

    public function delete(User $user, Ekstra $ekstra): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }
}
