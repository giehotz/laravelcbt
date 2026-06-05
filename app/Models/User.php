<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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
    use HasFactory, Notifiable, HasRoles;

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
        return $this->hasOne(\App\Models\Master\Guru::class);
    }

    /**
     * Get the siswa profile associated with the user.
     */
    public function siswa(): HasOne
    {
        return $this->hasOne(\App\Models\Master\Siswa::class);
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
}
