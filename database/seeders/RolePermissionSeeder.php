<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Master Data
            'master.siswa.view', 'master.siswa.create', 'master.siswa.edit', 'master.siswa.delete',
            'master.guru.view',  'master.guru.create',  'master.guru.edit',  'master.guru.delete',
            'master.kelas.view', 'master.kelas.create', 'master.kelas.edit', 'master.kelas.delete',
            'master.mapel.view', 'master.mapel.create', 'master.mapel.edit', 'master.mapel.delete',
            // CBT
            'cbt.bank.view', 'cbt.bank.create', 'cbt.bank.edit', 'cbt.bank.delete',
            'cbt.soal.view', 'cbt.soal.create', 'cbt.soal.edit', 'cbt.soal.delete',
            'cbt.jadwal.view', 'cbt.jadwal.create', 'cbt.jadwal.edit', 'cbt.jadwal.delete',
            'cbt.monitor', 'cbt.nilai.view', 'cbt.nilai.edit', 'cbt.rekap.view',
            // Akademik
            'rapor.view', 'rapor.edit', 'nilai.view', 'nilai.edit',
            // LMS
            'materi.view', 'materi.create', 'materi.edit', 'materi.delete',
            // Komunikasi
            'post.view', 'post.create', 'post.delete',
            // Buku Induk
            'buku_induk.view', 'buku_induk.edit',
            // Sistem
            'setting.view', 'setting.edit', 'log.view',
        ];

        foreach ($permissions as $permissionName) {
            Permission::create(['name' => $permissionName]);
        }

        // Define standard roles
        $superadmin = Role::create(['name' => 'superadmin']);
        $kepsek = Role::create(['name' => 'kepsek']);
        $operator = Role::create(['name' => 'operator']);
        $proktor = Role::create(['name' => 'proktor']);
        $guru = Role::create(['name' => 'guru']);
        $siswa = Role::create(['name' => 'siswa']);

        // Assign all permissions to superadmin
        $superadmin->syncPermissions(Permission::all());

        // Assign specific permissions to other roles
        $operator->syncPermissions([
            'master.siswa.view', 'master.siswa.create', 'master.siswa.edit', 'master.siswa.delete',
            'master.guru.view',  'master.guru.create',  'master.guru.edit',  'master.guru.delete',
            'master.kelas.view', 'master.kelas.create', 'master.kelas.edit', 'master.kelas.delete',
            'master.mapel.view', 'master.mapel.create', 'master.mapel.edit', 'master.mapel.delete',
            'buku_induk.view', 'buku_induk.edit',
            'post.view', 'post.create',
        ]);

        $guru->syncPermissions([
            'cbt.bank.view', 'cbt.bank.create', 'cbt.bank.edit', 'cbt.bank.delete',
            'cbt.soal.view', 'cbt.soal.create', 'cbt.soal.edit', 'cbt.soal.delete',
            'cbt.jadwal.view', 'cbt.jadwal.create',
            'cbt.nilai.view', 'cbt.nilai.edit',
            'rapor.view', 'rapor.edit',
            'materi.view', 'materi.create', 'materi.edit', 'materi.delete',
            'master.siswa.view', 'master.kelas.view', 'master.mapel.view',
            'post.view', 'post.create',
        ]);

        $kepsek->syncPermissions([
            'cbt.nilai.view', 'cbt.rekap.view',
            'rapor.view', 'nilai.view',
            'buku_induk.view',
        ]);

        $proktor->syncPermissions([
            'cbt.monitor', 'cbt.nilai.view', 'cbt.nilai.edit',
        ]);

        $siswa->syncPermissions([
            'materi.view', 'rapor.view',
        ]);

        // Create default test accounts
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@cbt.local',
                'username' => 'admin',
                'password' => bcrypt('password'),
                'role' => 'superadmin',
            ],
            [
                'name' => 'Operator TU',
                'email' => 'operator@cbt.local',
                'username' => 'operator',
                'password' => bcrypt('password'),
                'role' => 'operator',
            ],
            [
                'name' => 'Guru Pengajar',
                'email' => 'guru@cbt.local',
                'username' => 'guru',
                'password' => bcrypt('password'),
                'role' => 'guru',
            ],
            [
                'name' => 'Siswa Ujian',
                'email' => 'siswa@cbt.local',
                'username' => 'siswa',
                'password' => bcrypt('password'),
                'role' => 'siswa',
            ],
            [
                'name' => 'Kepala Sekolah',
                'email' => 'kepsek@cbt.local',
                'username' => 'kepsek',
                'password' => bcrypt('password'),
                'role' => 'kepsek',
            ],
            [
                'name' => 'Proktor Ujian',
                'email' => 'proktor@cbt.local',
                'username' => 'proktor',
                'password' => bcrypt('password'),
                'role' => 'proktor',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'username' => $userData['username'],
                'password' => $userData['password'],
            ]);
            $user->assignRole($userData['role']);
        }
    }
}
