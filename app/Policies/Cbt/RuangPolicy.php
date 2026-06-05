<?php

namespace App\Policies\Cbt;

use App\Models\Cbt\Ruang;
use App\Models\User;

class RuangPolicy
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
    public function view(User $user, Ruang $ruang): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ruang $ruang): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ruang $ruang): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }
}
