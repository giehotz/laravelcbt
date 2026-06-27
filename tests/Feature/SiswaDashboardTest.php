<?php

namespace Tests\Feature;

use App\Models\Master\Kelas;
use App\Models\Master\KelasSiswa;
use App\Models\Master\LevelKelas;
use App\Models\Master\Siswa;
use App\Models\Post;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SiswaDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $siswaUser;

    protected Siswa $siswa;

    protected Kelas $kelas;

    protected TahunPelajaran $tp;

    protected Semester $semester;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'superadmin']);
        Role::create(['name' => 'siswa']);

        $this->tp = TahunPelajaran::create(['tahun' => '2025/2026', 'active' => true]);
        $this->semester = Semester::create(['smt' => '1', 'nama_smt' => 'Ganjil', 'active' => true]);

        $level = LevelKelas::create(['level' => 'X']);
        $this->kelas = Kelas::create([
            'tahun_pelajaran_id' => $this->tp->id,
            'semester_id' => $this->semester->id,
            'nama_kelas' => 'X-IPA-1',
            'kode_kelas' => 'X-IPA-1',
            'level_id' => $level->id,
        ]);

        $this->siswaUser = User::factory()->create(['username' => 'siswa1']);
        $this->siswaUser->assignRole('siswa');
        $this->siswa = Siswa::create([
            'user_id' => $this->siswaUser->id,
            'nama' => 'Siswa Satu',
            'nisn' => '0011111111',
            'nis' => 'S001',
        ]);

        KelasSiswa::create([
            'tahun_pelajaran_id' => $this->tp->id,
            'semester_id' => $this->semester->id,
            'siswa_id' => $this->siswa->id,
            'kelas_id' => $this->kelas->id,
        ]);
    }

    public function test_siswa_dashboard_pengumuman_returns_filtered_posts()
    {
        $admin = User::factory()->create();

        // 1. Post targeted to everyone
        $postAll = Post::create([
            'dari_user_id' => $admin->id,
            'kepada' => ['type' => 'all'],
            'text' => 'Pengumuman untuk semua',
        ]);

        // 2. Post targeted to all siswa
        $postSiswa = Post::create([
            'dari_user_id' => $admin->id,
            'kepada' => ['type' => 'siswa'],
            'text' => 'Pengumuman untuk siswa',
        ]);

        // 3. Post targeted to this class
        $postKelas = Post::create([
            'dari_user_id' => $admin->id,
            'kepada' => ['type' => 'kelas', 'ids' => [$this->kelas->id]],
            'text' => 'Pengumuman untuk kelas X-IPA-1',
        ]);

        // 4. Post targeted to another class
        $postOtherKelas = Post::create([
            'dari_user_id' => $admin->id,
            'kepada' => ['type' => 'kelas', 'ids' => [999]],
            'text' => 'Pengumuman kelas lain',
        ]);

        // 5. Post targeted to guru
        $postGuru = Post::create([
            'dari_user_id' => $admin->id,
            'kepada' => ['type' => 'guru'],
            'text' => 'Pengumuman untuk guru',
        ]);

        $response = $this->actingAs($this->siswaUser)
            ->get(route('siswa.dashboard.pengumuman'));

        $response->assertOk();
        $response->assertJsonCount(3, 'data');

        $data = $response->json('data');

        // Verify that keys are mapped correctly to the expected Vue format (judul, isi, created_at)
        $kelasPost = collect($data)->firstWhere('isi', 'Pengumuman untuk kelas X-IPA-1');
        $this->assertNotNull($kelasPost);
        $this->assertEquals('Dari: '.$admin->name, $kelasPost['judul']);
        $this->assertArrayHasKey('created_at', $kelasPost);

        $texts = collect($data)->pluck('isi')->toArray();
        $this->assertContains('Pengumuman untuk semua', $texts);
        $this->assertContains('Pengumuman untuk siswa', $texts);
        $this->assertContains('Pengumuman untuk kelas X-IPA-1', $texts);
        $this->assertNotContains('Pengumuman kelas lain', $texts);
        $this->assertNotContains('Pengumuman untuk guru', $texts);
    }
}
