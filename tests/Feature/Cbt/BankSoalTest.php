<?php

namespace Tests\Feature\Cbt;

use App\Models\Cbt\BankSoal;
use App\Models\Cbt\Jenis;
use App\Models\Cbt\Soal;
use App\Models\Master\Mapel;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankSoalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup initial data required
        TahunPelajaran::unguard();
        Semester::unguard();
        Jenis::unguard();
        Mapel::unguard();

        TahunPelajaran::create(['id' => 1, 'tahun' => '2025/2026', 'active' => true]);
        Semester::create(['id' => 1, 'smt' => '1', 'nama_smt' => 'Ganjil', 'active' => true]);
        Jenis::create(['id' => 1, 'nama_jenis' => 'PAS', 'kode_jenis' => 'PAS']);
        Mapel::create(['id' => 1, 'nama_mapel' => 'Matematika']);
    }

    public function test_sync_jumlah_soal_automatically_updates_counts()
    {
        $bank = BankSoal::create([
            'jenis_id' => 1,
            'kode' => 'BANK-01',
            'level' => 'X',
            'kelas' => [1, 2],
            'mapel_id' => 1,
            'tahun_pelajaran_id' => 1,
            'semester_id' => 1,
            'nama' => 'Ujian MTK',
            'kkm' => 75,
            'status' => 0,
        ]);

        $this->assertEquals(0, $bank->jml_pg);
        $this->assertEquals(0, $bank->jml_esai);

        // Add PG Soal
        Soal::create([
            'bank_id' => $bank->id,
            'mapel_id' => 1,
            'jenis' => 1,
            'soal' => '1+1?',
            'opsi_a' => '1', 'opsi_b' => '2', 'opsi_c' => '3', 'opsi_d' => '4', 'opsi_e' => '5',
            'jawaban' => 'B'
        ]);

        $bank->refresh();
        $this->assertEquals(1, $bank->jml_pg);

        // Add Esai Soal
        Soal::create([
            'bank_id' => $bank->id,
            'mapel_id' => 1,
            'jenis' => 5,
            'soal' => 'Jelaskan!',
            'jawaban' => 'Penjelasan panjang'
        ]);

        $bank->refresh();
        $this->assertEquals(1, $bank->jml_pg);
        $this->assertEquals(1, $bank->jml_esai);
    }
}
