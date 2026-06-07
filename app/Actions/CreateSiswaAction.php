<?php

namespace App\Actions;

use App\Models\Master\BukuInduk;
use App\Models\Master\Siswa;
use App\Models\User;
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
                'email' => ! empty($data['email']) ? $data['email'] : $data['username'].'@sch.id',
                'username' => $data['username'],
                'password' => Hash::make($data['password'] ?? 'password'),
            ]);

            // Assign Siswa role
            $user->assignRole('siswa');

            // Create siswa profile linked to user
            $siswaData = [
                'user_id' => $user->id,
                'nisn' => $data['nisn'] ?? null,
                'nis' => $data['nis'] ?? null,
                'nama' => $data['nama'],
                'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                'tahun_masuk' => $data['tahun_masuk'] ?? null,
                'sekolah_asal' => $data['sekolah_asal'] ?? null,
                'tempat_lahir' => $data['tempat_lahir'] ?? null,
                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                'agama' => $data['agama'] ?? null,
                'hp' => $data['hp'] ?? null,
                'email' => $data['email'] ?? null,
                'foto' => $data['foto'] ?? 'siswa.png',
                'anak_ke' => $data['anak_ke'] ?? null,
                'status_keluarga' => $data['status_keluarga'] ?? null,
                'alamat' => $data['alamat'] ?? null,
                'rt' => $data['rt'] ?? null,
                'rw' => $data['rw'] ?? null,
                'kelurahan' => $data['kelurahan'] ?? null,
                'kecamatan' => $data['kecamatan'] ?? null,
                'kabupaten' => $data['kabupaten'] ?? null,
                'provinsi' => $data['provinsi'] ?? null,
                'kode_pos' => $data['kode_pos'] ?? null,
                'nama_ayah' => $data['nama_ayah'] ?? null,
                'tgl_lahir_ayah' => $data['tgl_lahir_ayah'] ?? null,
                'pendidikan_ayah' => $data['pendidikan_ayah'] ?? null,
                'pekerjaan_ayah' => $data['pekerjaan_ayah'] ?? null,
                'nohp_ayah' => $data['nohp_ayah'] ?? null,
                'alamat_ayah' => $data['alamat_ayah'] ?? null,
                'nama_ibu' => $data['nama_ibu'] ?? null,
                'tgl_lahir_ibu' => $data['tgl_lahir_ibu'] ?? null,
                'pendidikan_ibu' => $data['pendidikan_ibu'] ?? null,
                'pekerjaan_ibu' => $data['pekerjaan_ibu'] ?? null,
                'nohp_ibu' => $data['nohp_ibu'] ?? null,
                'alamat_ibu' => $data['alamat_ibu'] ?? null,
                'nama_wali' => $data['nama_wali'] ?? null,
                'tgl_lahir_wali' => $data['tgl_lahir_wali'] ?? null,
                'pendidikan_wali' => $data['pendidikan_wali'] ?? null,
                'pekerjaan_wali' => $data['pekerjaan_wali'] ?? null,
                'nohp_wali' => $data['nohp_wali'] ?? null,
                'alamat_wali' => $data['alamat_wali'] ?? null,
                'nik' => $data['nik'] ?? null,
                'warga_negara' => $data['warga_negara'] ?? 'WNI',
            ];
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
