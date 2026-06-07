<?php

namespace Tests\Feature\Cbt;

use App\Models\Cbt\Sesi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SesiTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'superadmin']);

        $this->superadmin = User::factory()->create();
        $this->superadmin->assignRole('superadmin');
    }

    public function test_can_view_sesi_ujian_list()
    {
        Sesi::factory()->count(2)->create();

        $response = $this->actingAs($this->superadmin)->get(route('cbt.sesi.index'));

        $response->assertStatus(200);
    }

    public function test_can_create_sesi_ujian()
    {
        $data = [
            'nama_sesi' => 'Sesi 1',
            'kode_sesi' => 'S1',
            'waktu_mulai' => '07:30',
            'waktu_akhir' => '09:30',
            'aktif' => true,
        ];

        $response = $this->actingAs($this->superadmin)->post(route('cbt.sesi.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('cbt_sesi', [
            'kode_sesi' => 'S1',
            'waktu_mulai' => '07:30',
        ]);
    }

    public function test_validates_waktu_akhir_must_be_after_waktu_mulai()
    {
        $data = [
            'nama_sesi' => 'Sesi Error',
            'kode_sesi' => 'ERR',
            'waktu_mulai' => '09:00',
            'waktu_akhir' => '08:00', // invalid
            'aktif' => true,
        ];

        $response = $this->actingAs($this->superadmin)->post(route('cbt.sesi.store'), $data);

        $response->assertSessionHasErrors('waktu_akhir');
    }

    public function test_can_update_sesi_ujian()
    {
        $sesi = Sesi::factory()->create(['waktu_mulai' => '07:00:00', 'waktu_akhir' => '09:00:00']);

        $response = $this->actingAs($this->superadmin)->put(route('cbt.sesi.update', $sesi), [
            'nama_sesi' => 'Sesi Ubah',
            'kode_sesi' => $sesi->kode_sesi,
            'waktu_mulai' => '08:00',
            'waktu_akhir' => '10:00',
            'aktif' => false,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cbt_sesi', [
            'id' => $sesi->id,
            'nama_sesi' => 'Sesi Ubah',
            'waktu_mulai' => '08:00',
            'aktif' => 0,
        ]);
    }

    public function test_cannot_delete_sesi_that_has_related_kelas_ruang_pseudo_test()
    {
        $sesi = Sesi::factory()->create();

        $response = $this->actingAs($this->superadmin)->delete(route('cbt.sesi.destroy', $sesi));

        $response->assertRedirect();
        $this->assertDatabaseMissing('cbt_sesi', ['id' => $sesi->id]);
    }
}
