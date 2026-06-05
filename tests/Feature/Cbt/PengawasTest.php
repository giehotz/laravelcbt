<?php

namespace Tests\Feature\Cbt;

use App\Models\Cbt\BankSoal;
use App\Models\Cbt\Jadwal;
use App\Models\Cbt\Jenis;
use App\Models\Cbt\KelasRuang;
use App\Models\Cbt\Pengawas;
use App\Models\Cbt\Ruang;
use App\Models\Cbt\Sesi;
use App\Models\Master\Guru;
use App\Models\Master\Kelas;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengawasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        \App\Models\Master\Mapel::unguard();
        TahunPelajaran::unguard();
        Semester::unguard();
        Jenis::unguard();
        BankSoal::unguard();
        Ruang::unguard();
        Sesi::unguard();
        Guru::unguard();

        $this->tp = TahunPelajaran::create(['id' => 1, 'tahun' => '2025/2026', 'active' => true]);
        $this->smt = Semester::create(['id' => 1, 'smt' => '1', 'nama_smt' => 'Ganjil', 'active' => true]);
        
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $this->guru1 = Guru::create(['id' => 1, 'nama_guru' => 'Guru A', 'user_id' => $user1->id]);
        $this->guru2 = Guru::create(['id' => 2, 'nama_guru' => 'Guru B', 'user_id' => $user2->id]);
        
        $this->ruang = Ruang::create(['id' => 1, 'kode_ruang' => 'R1', 'nama_ruang' => 'Ruang 1']);
        $this->sesi = Sesi::create(['id' => 1, 'kode_sesi' => 'S1', 'nama_sesi' => 'Sesi 1', 'waktu_mulai' => '08:00', 'waktu_akhir' => '10:00']);
        
        Jenis::create(['id' => 1, 'nama_jenis' => 'PAS', 'kode_jenis' => 'PAS']);
        \App\Models\Master\Mapel::create(['id' => 1, 'nama_mapel' => 'Matematika']);
        $bank = BankSoal::create([
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
        
        $this->jadwal = Jadwal::create([
            'tahun_pelajaran_id' => $this->tp->id,
            'semester_id' => $this->smt->id,
            'bank_id' => $bank->id,
            'tgl_mulai' => '2026-06-10 08:00:00',
            'tgl_selesai' => '2026-06-10 10:00:00',
            'durasi_ujian' => 60,
        ]);
    }

    public function test_can_sync_pengawas()
    {
        $user = User::factory()->create();
        if (!\Spatie\Permission\Models\Role::where('name', 'superadmin')->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => 'superadmin']);
        }
        $user->assignRole('superadmin');

        $payload = [
            'pengawas' => [
                [
                    'ruang_id' => $this->ruang->id,
                    'sesi_id' => $this->sesi->id,
                    'guru_id' => $this->guru1->id,
                ]
            ]
        ];

        $response = $this->actingAs($user)->post(route('cbt.pengawas.sync', $this->jadwal->id), $payload);

        $response->assertRedirect(route('cbt.pengawas.index'));
        
        $this->assertDatabaseHas('cbt_pengawas', [
            'jadwal_id' => $this->jadwal->id,
            'ruang_id' => $this->ruang->id,
            'sesi_id' => $this->sesi->id,
            'guru_id' => $this->guru1->id,
        ]);
    }

    public function test_prevents_pengawas_conflict()
    {
        $user = User::factory()->create();
        if (!\Spatie\Permission\Models\Role::where('name', 'superadmin')->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => 'superadmin']);
        }
        $user->assignRole('superadmin');

        // Assign guru1 to jadwal1
        Pengawas::create([
            'jadwal_id' => $this->jadwal->id,
            'ruang_id' => $this->ruang->id,
            'sesi_id' => $this->sesi->id,
            'guru_id' => $this->guru1->id,
        ]);

        // Create an overlapping jadwal
        $jadwal2 = Jadwal::create([
            'tahun_pelajaran_id' => $this->tp->id,
            'semester_id' => $this->smt->id,
            'bank_id' => 1,
            'tgl_mulai' => '2026-06-10 09:00:00', // Overlaps with 08:00-10:00
            'tgl_selesai' => '2026-06-10 11:00:00',
            'durasi_ujian' => 60,
        ]);

        $payload = [
            'pengawas' => [
                [
                    'ruang_id' => $this->ruang->id,
                    'sesi_id' => $this->sesi->id,
                    'guru_id' => $this->guru1->id, // Conflict!
                ]
            ]
        ];

        $response = $this->actingAs($user)->post(route('cbt.pengawas.sync', $jadwal2->id), $payload);

        $response->assertSessionHasErrors(['pengawas']);
        
        $this->assertDatabaseMissing('cbt_pengawas', [
            'jadwal_id' => $jadwal2->id,
            'guru_id' => $this->guru1->id,
        ]);
    }
}
