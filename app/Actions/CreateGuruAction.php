<?php

namespace App\Actions;

use App\Models\User;
use App\Models\Master\Guru;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateGuruAction
{
    /**
     * Execute the action to create a Guru and its associated User account.
     */
    public function execute(array $data): Guru
    {
        return DB::transaction(function () use ($data) {
            // Create user account
            $user = User::create([
                'name' => $data['nama_guru'],
                'email' => !empty($data['email']) ? $data['email'] : $data['username'] . '@sch.id',
                'username' => $data['username'],
                'password' => Hash::make($data['password'] ?? 'password'),
            ]);

            // Assign Guru role
            $user->assignRole('guru');

            // Create guru profile linked to user
            $guruData = array_merge($data, ['user_id' => $user->id]);
            return Guru::create($guruData);
        });
    }
}
