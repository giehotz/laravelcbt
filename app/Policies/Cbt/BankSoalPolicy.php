<?php

namespace App\Policies\Cbt;

use App\Models\Cbt\BankSoal;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BankSoalPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('superadmin|operator|kepsek')) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('guru');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BankSoal $bankSoal): bool
    {
        return $user->hasRole('guru') && $bankSoal->guru_id === $user->guru?->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('guru');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BankSoal $bankSoal): bool
    {
        return $user->hasRole('guru') && $bankSoal->guru_id === $user->guru?->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BankSoal $bankSoal): bool
    {
        return $user->hasRole('guru') && $bankSoal->guru_id === $user->guru?->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BankSoal $bankSoal): bool
    {
        return $user->hasRole('guru') && $bankSoal->guru_id === $user->guru?->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BankSoal $bankSoal): bool
    {
        return $user->hasRole('guru') && $bankSoal->guru_id === $user->guru?->id;
    }
}
