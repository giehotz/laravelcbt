<?php

namespace App\Policies\Cbt;

use App\Models\Cbt\Jenis;
use App\Models\User;

class JenisPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Read access is granted by route middleware
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Jenis $jenis): bool
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
    public function update(User $user, Jenis $jenis): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Jenis $jenis): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }
}
