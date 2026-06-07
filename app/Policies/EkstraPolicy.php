<?php

namespace App\Policies;

use App\Models\Master\Ekstra;
use App\Models\User;

class EkstraPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRoleLevel(40);
    }

    public function view(User $user, Ekstra $ekstra): bool
    {
        return $user->hasMinRoleLevel(40);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRoleLevel(60);
    }

    public function update(User $user, Ekstra $ekstra): bool
    {
        return $user->hasMinRoleLevel(60);
    }

    public function delete(User $user, Ekstra $ekstra): bool
    {
        return $user->hasMinRoleLevel(60);
    }
}
