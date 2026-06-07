<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\RoleHierarchy;
use App\Models\Master\Guru;
use App\Models\Master\Siswa;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'username', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the guru profile associated with the user.
     */
    public function guru(): HasOne
    {
        return $this->hasOne(Guru::class);
    }

    /**
     * Get the siswa profile associated with the user.
     */
    public function siswa(): HasOne
    {
        return $this->hasOne(Siswa::class);
    }

    /**
     * Scope to eager load guru profile.
     */
    public function scopeWithGuru($query)
    {
        return $query->with('guru');
    }

    /**
     * Scope to eager load siswa profile.
     */
    public function scopeWithSiswa($query)
    {
        return $query->with('siswa');
    }

    /**
     * Get the weight level of the highest role for the user.
     */
    public function getRoleLevel(): int
    {
        return $this->roles->max(fn ($role) => RoleHierarchy::getLevel($role->name)) ?? 0;
    }

    /**
     * Check if the user has at least the minimum role level weight.
     */
    public function hasMinRoleLevel(int $minLevel): bool
    {
        return $this->getRoleLevel() >= $minLevel;
    }
}
