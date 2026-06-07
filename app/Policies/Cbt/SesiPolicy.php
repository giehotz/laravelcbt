<?php

namespace App\Policies\Cbt;

use App\Models\Cbt\Sesi;
use App\Models\User;

class SesiPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Sesi $sesi): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasMinRoleLevel(60);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Sesi $sesi): bool
    {
        return $user->hasMinRoleLevel(60);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Sesi $sesi): bool
    {
        return $user->hasMinRoleLevel(60);
    }
}
