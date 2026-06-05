<?php

namespace App\Actions;

use App\Models\User;
use App\Models\Master\Siswa;
use App\Models\BukuInduk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateSiswaAction
{
    /**
     * Execute the action to create a Siswa, its associated User account, and initial BukuInduk record.
     */
    public function execute(array $data): Siswa
    {
        return DB::transaction(function () use ($data) {
            // Create user account
            $user = User::create([
                'name' => $data['nama'],
                'email' => !empty($data['email']) ? $data['email'] : $data['username'] . '@sch.id',
                'username' => $data['username'],
                'password' => Hash::make($data['password'] ?? 'password'),
            ]);

            // Assign Siswa role
            $user->assignRole('siswa');

            // Create siswa profile linked to user
            $siswaData = array_merge($data, ['user_id' => $user->id]);
            $siswa = Siswa::create($siswaData);

            // Create default BukuInduk record
            BukuInduk::create([
                'siswa_id' => $siswa->id,
                'uid' => $data['uid'] ?? null,
            ]);

            return $siswa;
        });
    }
}
