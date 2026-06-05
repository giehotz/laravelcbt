<?php

namespace App\Policies;

use App\Models\User;
use App\Enums\RoleHierarchy;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        return $user->hasAnyRole(['superadmin', 'operator']);
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
    public function update(User $user, User $model): bool
    {
        // A user can update themselves
        if ($user->id === $model->id) {
            return true;
        }

        // Otherwise check hierarchy levels
        return $this->hasHigherAuthority($user, $model);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // A user cannot delete themselves
        if ($user->id === $model->id) {
            return false;
        }

        return $this->hasHigherAuthority($user, $model);
    }

    /**
     * Check if the authenticated user has a higher role level than the target model.
     */
    private function hasHigherAuthority(User $user, User $model): bool
    {
        // Get highest role level for active user
        $userMaxLevel = $user->roles->max(fn ($role) => RoleHierarchy::getLevel($role->name));

        // Get highest role level for target user
        $modelMaxLevel = $model->roles->max(fn ($role) => RoleHierarchy::getLevel($role->name));

        // Authenticated user must have strictly higher hierarchy level than target
        return $userMaxLevel > $modelMaxLevel;
    }
}
