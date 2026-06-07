<?php

namespace App\Actions;

use App\Models\Master\Guru;
use App\Models\User;
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
                'email' => ! empty($data['email']) ? $data['email'] : $data['username'].'@sch.id',
                'username' => $data['username'],
                'password' => Hash::make($data['password'] ?? 'password'),
            ]);

            // Assign Guru role
            $user->assignRole('guru');

            // Create guru profile linked to user
            $guruData = [
                'user_id' => $user->id,
                'nip' => $data['nip'] ?? null,
                'nama_guru' => $data['nama_guru'],
                'email' => $data['email'] ?? null,
                'kode_guru' => $data['kode_guru'] ?? null,
                'no_ktp' => $data['no_ktp'] ?? null,
                'tempat_lahir' => $data['tempat_lahir'] ?? null,
                'tgl_lahir' => $data['tgl_lahir'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                'agama' => $data['agama'] ?? null,
                'no_hp' => $data['no_hp'] ?? null,
                'alamat' => $data['alamat'] ?? null,
                'nuptk' => $data['nuptk'] ?? null,
                'jenis_ptk' => $data['jenis_ptk'] ?? null,
                'status_pegawai' => $data['status_pegawai'] ?? null,
                'status_aktif' => $data['status_aktif'] ?? 'Aktif',
                'foto' => $data['foto'] ?? null,
            ];

            return Guru::create($guruData);
        });
    }
}
