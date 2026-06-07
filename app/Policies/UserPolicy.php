<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRoleLevel(60);
    }

    public function view(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        return $user->hasMinRoleLevel(60);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRoleLevel(60);
    }

    public function update(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        return $this->hasHigherAuthority($user, $model);
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        return $this->hasHigherAuthority($user, $model);
    }

    private function hasHigherAuthority(User $user, User $model): bool
    {
        return $user->getRoleLevel() > $model->getRoleLevel();
    }
}
