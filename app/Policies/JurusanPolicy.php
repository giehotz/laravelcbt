<?php

namespace App\Policies;

use App\Models\Master\Jurusan;
use App\Models\User;

class JurusanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRoleLevel(40);
    }

    public function view(User $user, Jurusan $jurusan): bool
    {
        return $user->hasMinRoleLevel(40);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRoleLevel(60);
    }

    public function update(User $user, Jurusan $jurusan): bool
    {
        return $user->hasMinRoleLevel(60);
    }

    public function delete(User $user, Jurusan $jurusan): bool
    {
        return $user->hasMinRoleLevel(60);
    }
}
