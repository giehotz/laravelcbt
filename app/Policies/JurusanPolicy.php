<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Master\Jurusan;

class JurusanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator', 'guru']);
    }

    public function view(User $user, Jurusan $jurusan): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator', 'guru']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }

    public function update(User $user, Jurusan $jurusan): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }

    public function delete(User $user, Jurusan $jurusan): bool
    {
        return $user->hasAnyRole(['superadmin', 'operator']);
    }
}
