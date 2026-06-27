<?php

namespace App\Policies\Cbt;

use App\Models\Cbt\SoalSiswa;
use App\Models\User;

class SoalSiswaPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SoalSiswa $soalSiswa): bool
    {
        return $user->siswa && $soalSiswa->siswa_id === $user->siswa->id;
    }
}
