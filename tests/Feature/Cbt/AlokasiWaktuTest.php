<?php

namespace Tests\Feature\Cbt;

use App\Models\Cbt\BankSoal;
use App\Models\Cbt\DurasiSiswa;
use App\Models\Cbt\Jadwal;
use App\Models\Cbt\Jenis;
use App\Models\Master\Kelas;
use App\Models\Master\Siswa;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlokasiWaktuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        
        \App\Models\Master\Mapel::unguard();
        TahunPelajaran::unguard();
        Semester::unguard();
        Jenis::unguard();
        BankSoal::unguard();
        Kelas::unguard();
        Siswa::unguard();

        \App\Models\Master\LevelKelas::unguard();
        \App\Models\Master\Jurusan::unguard();
        \App\Models\Master\Guru::unguard();
        
        \App\Models\Master\LevelKelas::create(['id' => 1, 'level' => 'X']);
        \App\Models\Master\Jurusan::create(['id' => 1, 'kode_jurusan' => 'IPA', 'nama_jurusan' => 'IPA']);
        \App\Models\Master\Guru::create(['id' => 1, 'nama_guru' => 'Guru']);

        $this->tp = TahunPelajaran::create(['id' => 1, 'tahun' => '2025/2026', 'active' => true]);
        $this->smt = Semester::create(['id' => 1, 'smt' => '1', 'nama_smt' => 'Ganjil', 'active' => true]);
        
        $this->kelas = Kelas::create(['id' => 1, 'nama_kelas' => 'XA', 'tahun_pelajaran_id' => 1, 'semester_id' => 1, 'level_id' => 1, 'jurusan_id' => 1, 'guru_id' => 1]);
        
        $user1 = User::factory()->create();
        $this->siswa = Siswa::create([
            'id' => 1, 
            'nama' => 'Siswa A', 
            'nisn' => '123', 
            'nis' => '123', 
            'user_id' => $user1->id
        ]);
        
        \App\Models\Master\KelasSiswa::unguard();
        \App\Models\Master\KelasSiswa::create([
            'tahun_pelajaran_id' => 1,
            'semester_id' => 1,
            'kelas_id' => 1,
            'siswa_id' => 1,
        ]);
        
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

        $this->user = User::factory()->create();
        if (!\Spatie\Permission\Models\Role::where('name', 'superadmin')->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => 'superadmin']);
        }
        $this->user->assignRole('superadmin');
    }

    public function test_can_view_alokasi_waktu_index()
    {
        $response = $this->actingAs($this->user)->get(route('cbt.alokasi-waktu.index'));
        $response->assertStatus(200);
    }

    public function test_can_generate_alokasi_waktu()
    {
        $response = $this->actingAs($this->user)->post(route('cbt.alokasi-waktu.generate', $this->jadwal->id));
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('cbt_durasi_siswa', [
            'jadwal_id' => $this->jadwal->id,
            'siswa_id' => $this->siswa->id,
            'status' => 0,
        ]);
    }
}
