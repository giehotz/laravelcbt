<?php

namespace App\Enums;

class RoleHierarchy
{
    const LEVELS = [
        'superadmin' => 100,
        'kepsek' => 80,
        'operator' => 60,
        'proktor' => 50,
        'guru' => 40,
        'siswa' => 10,
    ];

    /**
     * Get the weight level of a role.
     */
    public static function getLevel(string $role): int
    {
        return self::LEVELS[strtolower($role)] ?? 0;
    }
}
