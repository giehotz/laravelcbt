<?php

namespace Tests\Feature\Cbt;

use App\Models\Cbt\Jenis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JenisTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected User $guru;

    protected function setUp(): void
    {
        parent::setUp();
        
        \Spatie\Permission\Models\Role::create(['name' => 'superadmin']);
        \Spatie\Permission\Models\Role::create(['name' => 'guru']);
        
        $this->superadmin = User::factory()->create();
        $this->superadmin->assignRole('superadmin');
        
        $this->guru = User::factory()->create();
        $this->guru->assignRole('guru');
    }

    public function test_can_view_jenis_ujian_list_as_superadmin()
    {
        Jenis::factory()->count(3)->create();

        $response = $this->actingAs($this->superadmin)->get(route('cbt.jenis.index'));

        $response->assertStatus(200);
    }

    public function test_can_create_jenis_ujian_as_superadmin()
    {
        $data = [
            'nama_jenis' => 'Penilaian Akhir Semester',
            'kode_jenis' => 'PAS',
        ];

        $response = $this->actingAs($this->superadmin)->post(route('cbt.jenis.store'), $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('cbt_jenis', $data);
    }

    public function test_cannot_create_jenis_ujian_as_guru()
    {
        $data = [
            'nama_jenis' => 'Penilaian Akhir Semester',
            'kode_jenis' => 'PAS',
        ];

        $response = $this->actingAs($this->guru)->post(route('cbt.jenis.store'), $data);

        $response->assertForbidden(); // Due to Policy
    }

    public function test_validates_unique_kode_jenis_on_creation()
    {
        Jenis::factory()->create(['kode_jenis' => 'PAS']);

        $response = $this->actingAs($this->superadmin)->post(route('cbt.jenis.store'), [
            'nama_jenis' => 'Another Test',
            'kode_jenis' => 'PAS',
        ]);

        $response->assertSessionHasErrors('kode_jenis');
    }

    public function test_can_update_jenis_ujian()
    {
        $jenis = Jenis::factory()->create();

        $response = $this->actingAs($this->superadmin)->put(route('cbt.jenis.update', $jenis), [
            'nama_jenis' => 'Ujian Baru',
            'kode_jenis' => 'UBR',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cbt_jenis', [
            'id' => $jenis->id,
            'nama_jenis' => 'Ujian Baru',
        ]);
    }

    public function test_can_delete_jenis_ujian()
    {
        $jenis = Jenis::factory()->create();

        $response = $this->actingAs($this->superadmin)->delete(route('cbt.jenis.destroy', $jenis));

        $response->assertRedirect();
        $this->assertDatabaseMissing('cbt_jenis', ['id' => $jenis->id]);
    }
}
