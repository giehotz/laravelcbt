<?php

namespace Tests\Feature\Cbt;

use App\Models\Cbt\BankSoal;
use App\Models\Cbt\Jenis;
use App\Models\Master\Mapel;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class JadwalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mapel::unguard();
        TahunPelajaran::unguard();
        Semester::unguard();
        Jenis::unguard();
        BankSoal::unguard();

        Mapel::create(['id' => 1, 'nama_mapel' => 'Matematika']);
        TahunPelajaran::create(['id' => 1, 'tahun' => '2025/2026', 'active' => true]);
        Semester::create(['id' => 1, 'smt' => '1', 'nama_smt' => 'Ganjil', 'active' => true]);
        Jenis::create(['id' => 1, 'nama_jenis' => 'PAS', 'kode_jenis' => 'PAS']);
        BankSoal::create([
            'id' => 1,
            'kode' => 'B-01',
            'nama' => 'Ujian',
            'tahun_pelajaran_id' => 1,
            'semester_id' => 1,
            'jenis_id' => 1,
            'status' => 1,
            'level' => 'X',
            'kelas' => [1],
            'mapel_id' => 1,
            'kkm' => 70,
        ]);

        $user = User::factory()->create();
        // create role if not exist
        if (! Role::where('name', 'superadmin')->exists()) {
            Role::create(['name' => 'superadmin']);
        }
        $user->assignRole('superadmin');
        $this->actingAs($user);
    }

    public function test_jadwal_requires_tgl_selesai_after_tgl_mulai()
    {
        $response = $this->post(route('cbt.jadwal.store'), [
            'bank_id' => 1,
            'jenis_id' => 1,
            'tgl_mulai' => '2026-06-10 10:00:00',
            'tgl_selesai' => '2026-06-10 09:00:00', // Invalid
            'durasi_ujian' => 60,
            'status' => 1,
        ]);

        $response->assertSessionHasErrors(['tgl_selesai']);
    }

    public function test_jadwal_creates_successfully_with_valid_dates()
    {
        $response = $this->post(route('cbt.jadwal.store'), [
            'bank_id' => 1,
            'jenis_id' => 1,
            'tgl_mulai' => '2026-06-10 08:00:00',
            'tgl_selesai' => '2026-06-10 10:00:00', // Valid
            'durasi_ujian' => 90,
            'status' => 1,
            'acak_soal' => true,
            'acak_opsi' => true,
        ]);

        $response->assertRedirect(route('cbt.jadwal.index'));
        $this->assertDatabaseHas('cbt_jadwal', [
            'bank_id' => 1,
            'durasi_ujian' => 90,
        ]);
    }
}
