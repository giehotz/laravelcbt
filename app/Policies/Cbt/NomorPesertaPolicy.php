<?php

namespace App\Policies\Cbt;

use App\Models\Cbt\NomorPeserta;
use App\Models\User;

class NomorPesertaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function generate(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }
}
