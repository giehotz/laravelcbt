<?php

namespace Tests\Feature\Cbt;

use App\Models\Master\Kelas;
use App\Models\Master\KelasSiswa;
use App\Models\Master\Siswa;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NomorPesertaTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;

    protected User $guru;

    protected TahunPelajaran $tp;

    protected Semester $smt;

    protected function setUp(): void
    {
        parent::setUp();

        // Create superadmin
        $this->superadmin = User::factory()->create();
        Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $this->superadmin->assignRole('superadmin');

        // Create guru
        $this->guru = User::factory()->create();
        Role::firstOrCreate(['name' => 'guru', 'guard_name' => 'web']);
        $this->guru->assignRole('guru');

        $this->tp = TahunPelajaran::factory()->create(['active' => true]);
        $this->smt = Semester::factory()->create(['active' => true]);
    }

    public function test_can_view_nomor_peserta_page_as_superadmin()
    {
        $response = $this->actingAs($this->superadmin)->get(route('cbt.nomor-peserta.index'));
        $response->assertStatus(200);
    }

    public function test_cannot_view_nomor_peserta_page_as_guru()
    {
        $response = $this->actingAs($this->guru)->get(route('cbt.nomor-peserta.index'));
        $response->assertStatus(403);
    }

    public function test_can_generate_nomor_peserta_deterministically()
    {
        $kelas = Kelas::factory()->create(['nama_kelas' => 'X MIPA 1']);

        $siswa1 = Siswa::factory()->create(['nama' => 'Budi']);
        $siswa2 = Siswa::factory()->create(['nama' => 'Andi']);

        KelasSiswa::create(['kelas_id' => $kelas->id, 'siswa_id' => $siswa1->id, 'tahun_pelajaran_id' => $this->tp->id, 'semester_id' => $this->smt->id]);
        KelasSiswa::create(['kelas_id' => $kelas->id, 'siswa_id' => $siswa2->id, 'tahun_pelajaran_id' => $this->tp->id, 'semester_id' => $this->smt->id]);

        $response = $this->actingAs($this->superadmin)->post(route('cbt.nomor-peserta.generate'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Andi should be 0001, Budi should be 0002 because A comes before B
        $this->assertDatabaseHas('cbt_nomor_peserta', [
            'siswa_id' => $siswa2->id,
            'nomor_peserta' => '0001',
        ]);

        $this->assertDatabaseHas('cbt_nomor_peserta', [
            'siswa_id' => $siswa1->id,
            'nomor_peserta' => '0002',
        ]);
    }
}
