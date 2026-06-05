<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Master\Siswa;
use App\Models\Cbt\Jadwal;
use App\Models\Cbt\BankSoal;
use App\Models\Cbt\Soal;
use App\Models\CbtSoalSiswa;
use App\Models\CbtNilai;
use App\Services\CbtService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class CbtServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Config::set('database.connections.sqlite.foreign_key_constraints', false);
        Schema::disableForeignKeyConstraints();

        // Buat parent rows untuk foreign keys yang sangat strict di SQLite memory
        DB::table('cbt_jenis')->insert(['id' => 1, 'nama_jenis' => 'UH', 'kode_jenis' => 'UH']);
        DB::table('tahun_pelajaran')->insert(['id' => 1, 'tahun' => '2025/2026', 'active' => true]);
        DB::table('semesters')->insert(['id' => 1, 'smt' => '1', 'nama_smt' => 'Ganjil', 'active' => true]);
    }

    public function test_distribusi_soal_acak_dan_alias_opsi()
    {
        $siswa = Siswa::forceCreate([
            'nisn' => '111',
            'nis' => '111',
            'nama' => 'Test Siswa 1'
        ]);

        $bank = BankSoal::forceCreate([
            'nama' => 'Bank 1',
            'jenis_id' => 1,
            'kode' => 'B01',
            'level' => '1',
            'kelas' => '[]',
            'tahun_pelajaran_id' => 1,
            'semester_id' => 1,
            'jml_pg' => 2,
            'bobot_pg' => 50,
            'jml_esai' => 0,
        ]);

        $soal1 = Soal::forceCreate([
            'bank_id' => $bank->id,
            'jenis' => 1, // PG
            'jawaban' => 'A', // Original answer is A
        ]);

        $soal2 = Soal::forceCreate([
            'bank_id' => $bank->id,
            'jenis' => 1, // PG
            'jawaban' => 'B', // Original answer is B
        ]);

        $jadwal = Jadwal::forceCreate([
            'bank_id' => $bank->id,
            'tahun_pelajaran_id' => 1,
            'semester_id' => 1,
            'acak_soal' => true,
            'acak_opsi' => true, // We will test aliasing!
            'tgl_mulai' => now()->subHour()->toISOString(),
            'tgl_selesai' => now()->addHour()->toISOString(),
            'durasi_ujian' => 120,
            'status' => 1,
            'hasil_tampil' => true
        ]);

        $service = new CbtService();
        $service->distribusiSoal($siswa, $jadwal);

        $soalSiswas = CbtSoalSiswa::where('siswa_id', $siswa->id)
            ->where('jadwal_id', $jadwal->id)
            ->get();

        $this->assertCount(2, $soalSiswas);

        // Check if one of them has soal_end = true
        $this->assertTrue($soalSiswas->where('soal_end', true)->count() === 1);

        // Test option aliasing
        foreach ($soalSiswas as $ss) {
            $this->assertNotNull($ss->opsi_alias_a, 'Opsi A alias must be generated');
            $this->assertNotNull($ss->jawaban_alias, 'Jawaban alias must be generated');
            
            // Verifikasi logika alias
            $this->assertTrue(in_array($ss->jawaban_alias, ['A', 'B', 'C', 'D', 'E']));
        }
    }

    public function test_hitung_nilai_otomatis_menghitung_benar()
    {
        $siswa = Siswa::forceCreate([
            'nisn' => '222',
            'nis' => '222',
            'nama' => 'Test Siswa 2'
        ]);

        $bank = BankSoal::forceCreate([
            'nama' => 'Bank 2',
            'jenis_id' => 1,
            'kode' => 'B02',
            'level' => '1',
            'kelas' => '[]',
            'tahun_pelajaran_id' => 1,
            'semester_id' => 1,
            'jml_pg' => 2,
            'bobot_pg' => 100, // Total possible PG score is 100 (50 each)
        ]);

        $jadwal = Jadwal::forceCreate([
            'bank_id' => $bank->id,
            'tahun_pelajaran_id' => 1,
            'semester_id' => 1,
            'tgl_mulai' => now()->subHour()->toISOString(),
            'tgl_selesai' => now()->addHour()->toISOString(),
            'durasi_ujian' => 120,
        ]);

        Soal::forceCreate([
            'id' => 1,
            'bank_id' => $bank->id,
            'jenis' => 1,
        ]);

        Soal::forceCreate([
            'id' => 2,
            'bank_id' => $bank->id,
            'jenis' => 1,
        ]);

        // Mock 2 soal siswa (1 benar, 1 salah)
        CbtSoalSiswa::forceCreate([
            'id' => 'u1',
            'bank_id' => $bank->id,
            'jadwal_id' => $jadwal->id,
            'siswa_id' => $siswa->id,
            'soal_id' => 1,
            'jenis_soal' => 1,
            'no_soal_alias' => 1,
            'jawaban_benar' => 'A',
            'jawaban_alias' => 'C',
            'jawaban_siswa' => 'C', // Benar
        ]);

        CbtSoalSiswa::forceCreate([
            'id' => 'u2',
            'bank_id' => $bank->id,
            'jadwal_id' => $jadwal->id,
            'siswa_id' => $siswa->id,
            'soal_id' => 2,
            'jenis_soal' => 1,
            'no_soal_alias' => 2,
            'jawaban_benar' => 'B',
            'jawaban_alias' => 'D',
            'jawaban_siswa' => 'A', // Salah (harusnya D)
        ]);

        $service = new CbtService();
        $service->hitungNilai($siswa, $jadwal);

        $nilai = CbtNilai::where('siswa_id', $siswa->id)
            ->where('jadwal_id', $jadwal->id)
            ->first();

        $this->assertNotNull($nilai);
        $this->assertEquals(1, $nilai->pg_benar); // 1 benar
        // Bobot = 100, Soal = 2 -> 1 soal = 50
        $this->assertEquals(50.00, (float) $nilai->pg_nilai);
        $this->assertEquals(0.00, (float) $nilai->esai_nilai);
        $this->assertFalse($nilai->dikoreksi);
    }
}
