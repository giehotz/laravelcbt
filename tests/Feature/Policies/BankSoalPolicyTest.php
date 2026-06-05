<?php

namespace Tests\Feature\Policies;

use App\Models\Cbt\BankSoal;
use App\Models\Master\Guru;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BankSoalPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'guru']);
        Role::firstOrCreate(['name' => 'superadmin']);
    }

    public function test_guru_can_update_own_bank_soal()
    {
        $user = User::factory()->create();
        $user->assignRole('guru');
        $guru = Guru::factory()->create(['user_id' => $user->id]);
        
        $bankSoal = BankSoal::factory()->create(['guru_id' => $guru->id]);

        $this->assertTrue($user->can('update', $bankSoal));
    }

    public function test_guru_cannot_update_others_bank_soal()
    {
        $user1 = User::factory()->create();
        $user1->assignRole('guru');
        $guru1 = Guru::factory()->create(['user_id' => $user1->id]);

        $user2 = User::factory()->create();
        $user2->assignRole('guru');
        $guru2 = Guru::factory()->create(['user_id' => $user2->id]);
        
        $bankSoal = BankSoal::factory()->create(['guru_id' => $guru2->id]);

        $this->assertFalse($user1->can('update', $bankSoal));
    }

    public function test_superadmin_can_update_any_bank_soal()
    {
        $user = User::factory()->create();
        $user->assignRole('superadmin');
        
        $bankSoal = BankSoal::factory()->create();

        $this->assertTrue($user->can('update', $bankSoal));
    }
}
