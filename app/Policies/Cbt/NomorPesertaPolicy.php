<?php

namespace App\Policies\Cbt;

use App\Models\User;

class NomorPesertaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRoleLevel(60);
    }

    public function generate(User $user): bool
    {
        return $user->hasMinRoleLevel(60);
    }
}
