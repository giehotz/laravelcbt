<?php

namespace Tests\Feature\Cbt;

use App\Jobs\Cbt\SyncSesiSiswaJob;
use App\Models\Cbt\KelasRuang;
use App\Models\Cbt\Ruang;
use App\Models\Cbt\Sesi;
use App\Models\Master\Kelas;
use App\Models\Master\KelasSiswa;
use App\Models\Master\Siswa;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AturRuangSesiTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;

    protected TahunPelajaran $tp;

    protected Semester $smt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::factory()->create();
        Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $this->superadmin->assignRole('superadmin');

        $this->tp = TahunPelajaran::factory()->create(['active' => true]);
        $this->smt = Semester::factory()->create(['active' => true]);
    }

    public function test_can_view_atur_ruang_page()
    {
        $response = $this->actingAs($this->superadmin)->get(route('cbt.atur-ruang.index'));
        $response->assertStatus(200);
    }

    public function test_can_store_kelas_ruang_and_dispatch_job()
    {
        Queue::fake();

        $kelas = Kelas::factory()->create();
        $ruang = Ruang::factory()->create();
        $sesi = Sesi::factory()->create();

        $response = $this->actingAs($this->superadmin)->post(route('cbt.atur-ruang.store'), [
            'kelas_id' => $kelas->id,
            'ruang_id' => $ruang->id,
            'sesi_id' => $sesi->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cbt_kelas_ruang', [
            'kelas_id' => $kelas->id,
            'ruang_id' => $ruang->id,
            'sesi_id' => $sesi->id,
        ]);

        Queue::assertPushed(SyncSesiSiswaJob::class);
    }

    public function test_sync_job_inserts_sesi_siswa_correctly()
    {
        $kelas = Kelas::factory()->create();
        $siswa = Siswa::factory()->create();
        KelasSiswa::create(['kelas_id' => $kelas->id, 'siswa_id' => $siswa->id, 'tahun_pelajaran_id' => $this->tp->id, 'semester_id' => $this->smt->id]);

        $ruang = Ruang::factory()->create();
        $sesi = Sesi::factory()->create();

        $kelasRuang = KelasRuang::create([
            'kelas_id' => $kelas->id,
            'ruang_id' => $ruang->id,
            'sesi_id' => $sesi->id,
            'tp_id' => $this->tp->id,
            'smt_id' => $this->smt->id,
        ]);

        // Run job
        $job = new SyncSesiSiswaJob($kelasRuang);
        $job->handle();

        $this->assertDatabaseHas('cbt_sesi_siswa', [
            'siswa_id' => $siswa->id,
            'kelas_id' => $kelas->id,
            'ruang_id' => $ruang->id,
            'sesi_id' => $sesi->id,
            'is_manual' => 0,
        ]);
    }
}
