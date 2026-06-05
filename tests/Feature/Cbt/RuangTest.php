<?php

namespace Tests\Feature\Cbt;

use App\Models\Cbt\Ruang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuangTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();
        
        \Spatie\Permission\Models\Role::create(['name' => 'superadmin']);
        
        $this->superadmin = User::factory()->create();
        $this->superadmin->assignRole('superadmin');
    }

    public function test_can_view_ruang_ujian_list()
    {
        Ruang::factory()->count(2)->create();

        $response = $this->actingAs($this->superadmin)->get(route('cbt.ruang.index'));

        $response->assertStatus(200);
    }

    public function test_can_create_ruang_ujian()
    {
        $data = [
            'nama_ruang' => 'Lab Komputer A',
            'kode_ruang' => 'LAB-A',
        ];

        $response = $this->actingAs($this->superadmin)->post(route('cbt.ruang.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('cbt_ruang', $data);
    }

    public function test_can_update_ruang_ujian()
    {
        $ruang = Ruang::factory()->create();

        $response = $this->actingAs($this->superadmin)->put(route('cbt.ruang.update', $ruang), [
            'nama_ruang' => 'Ruang Baru',
            'kode_ruang' => 'RB',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cbt_ruang', [
            'id' => $ruang->id,
            'nama_ruang' => 'Ruang Baru',
        ]);
    }

    public function test_can_delete_ruang_ujian()
    {
        $ruang = Ruang::factory()->create();

        $response = $this->actingAs($this->superadmin)->delete(route('cbt.ruang.destroy', $ruang));

        $response->assertRedirect();
        $this->assertDatabaseMissing('cbt_ruang', ['id' => $ruang->id]);
    }
}
