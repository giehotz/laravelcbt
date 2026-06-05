<?php

namespace App\Policies\Cbt;

use App\Models\Cbt\KelasRuang;
use App\Models\User;

class KelasRuangPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function store(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }

    public function update(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }
}
