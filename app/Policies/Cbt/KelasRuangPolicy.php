<?php

namespace App\Policies\Cbt;

use App\Models\User;

class KelasRuangPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function store(User $user): bool
    {
        return $user->hasMinRoleLevel(60);
    }

    public function update(User $user): bool
    {
        return $user->hasMinRoleLevel(60);
    }
}
