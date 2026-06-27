<?php

namespace Tests\Feature;

use App\Enums\Cbt\DurasiStatus;
use App\Models\Cbt\BankSoal;
use App\Models\Cbt\DurasiSiswa;
use App\Models\Cbt\Jadwal;
use App\Models\Cbt\Nilai;
use App\Models\Cbt\Soal;
use App\Models\Cbt\SoalSiswa;
use App\Models\Master\Siswa;
use App\Services\CbtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

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
        DB::table('level_kelas')->insert(['id' => 1, 'level' => 'X']);
        DB::table('kelas')->insert([
            'id' => 10,
            'tahun_pelajaran_id' => 1,
            'semester_id' => 1,
            'level_id' => 1,
            'nama_kelas' => 'X-IPA-1',
        ]);
    }

    public function test_distribusi_soal_acak_dan_alias_opsi()
    {
        $siswa = Siswa::forceCreate([
            'nisn' => '111',
            'nis' => '111',
            'nama' => 'Test Siswa 1',
        ]);

        $bank = BankSoal::forceCreate([
            'nama' => 'Bank 1',
            'jenis_id' => 1,
            'kode' => 'B01',
            'level' => '1',
            'kelas' => json_encode([10]),
            'tahun_pelajaran_id' => 1,
            'semester_id' => 1,
            'jml_pg' => 2,
            'bobot_pg' => 50,
            'jml_esai' => 0,
        ]);

        // Hubungkan siswa ke kelas target
        DB::table('kelas_siswa')->insert([
            'tahun_pelajaran_id' => 1,
            'semester_id' => 1,
            'siswa_id' => $siswa->id,
            'kelas_id' => 10,
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
            'hasil_tampil' => true,
        ]);

        $service = new CbtService;
        $service->distribusiSoal($siswa, $jadwal);

        $soalSiswas = SoalSiswa::where('siswa_id', $siswa->id)
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
            'nama' => 'Test Siswa 2',
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
        SoalSiswa::forceCreate([
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

        SoalSiswa::forceCreate([
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

        $service = new CbtService;
        $service->hitungNilai($siswa, $jadwal);

        $nilai = Nilai::where('siswa_id', $siswa->id)
            ->where('jadwal_id', $jadwal->id)
            ->first();

        $this->assertNotNull($nilai);
        $this->assertEquals(1, $nilai->pg_benar); // 1 benar
        // Bobot = 100, Soal = 2 -> 1 soal = 50
        $this->assertEquals(50.00, (float) $nilai->pg_nilai);
        $this->assertEquals(0.00, (float) $nilai->esai_nilai);
        $this->assertFalse($nilai->dikoreksi);
    }

    public function test_simpan_jawaban_berhasil()
    {
        $siswa = Siswa::forceCreate([
            'nisn' => '333',
            'nis' => '333',
            'nama' => 'Test Siswa 3',
        ]);

        $bank = BankSoal::forceCreate([
            'nama' => 'Bank 3',
            'jenis_id' => 1,
            'kode' => 'B03',
            'level' => '1',
            'kelas' => '[]',
            'tahun_pelajaran_id' => 1,
            'semester_id' => 1,
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

        $soalSiswa = SoalSiswa::forceCreate([
            'id' => 'u3',
            'bank_id' => $bank->id,
            'jadwal_id' => $jadwal->id,
            'siswa_id' => $siswa->id,
            'soal_id' => 1,
            'jenis_soal' => 1,
            'no_soal_alias' => 1,
        ]);

        $durasi = new DurasiSiswa([
            'siswa_id' => $siswa->id,
            'jadwal_id' => $jadwal->id,
            'mulai' => now()->toTimeString(),
        ]);
        $durasi->status = DurasiStatus::SEDANG;
        $durasi->save();

        $service = new CbtService;
        $service->simpanJawaban($soalSiswa, 'A', false, $siswa);

        $soalSiswa->refresh();
        $this->assertEquals('A', $soalSiswa->jawaban_siswa);
        $this->assertFalse($soalSiswa->ragu_ragu);
    }

    public function test_simpan_jawaban_menolak_jika_waktu_habis()
    {
        $siswa = Siswa::forceCreate([
            'nisn' => '444',
            'nis' => '444',
            'nama' => 'Test Siswa 4',
        ]);

        $bank = BankSoal::forceCreate([
            'nama' => 'Bank 4',
            'jenis_id' => 1,
            'kode' => 'B04',
            'level' => '1',
            'kelas' => '[]',
            'tahun_pelajaran_id' => 1,
            'semester_id' => 1,
            'jml_pg' => 1,
        ]);

        $jadwal = Jadwal::forceCreate([
            'bank_id' => $bank->id,
            'tahun_pelajaran_id' => 1,
            'semester_id' => 1,
            'tgl_mulai' => now()->subHours(2)->toISOString(),
            'tgl_selesai' => now()->addHour()->toISOString(),
            'durasi_ujian' => 60, // 60 menit
        ]);

        Soal::forceCreate([
            'id' => 10,
            'bank_id' => $bank->id,
            'jenis' => 1,
        ]);

        $soalSiswa = SoalSiswa::forceCreate([
            'id' => 'u4',
            'bank_id' => $bank->id,
            'jadwal_id' => $jadwal->id,
            'siswa_id' => $siswa->id,
            'soal_id' => 10,
            'jenis_soal' => 1,
            'no_soal_alias' => 1,
        ]);

        // Sesi mulai 90 menit lalu (melebihi durasi ujian 60 menit)
        $durasi = new DurasiSiswa([
            'siswa_id' => $siswa->id,
            'jadwal_id' => $jadwal->id,
            'mulai' => now()->subMinutes(90)->toTimeString(),
        ]);
        $durasi->status = DurasiStatus::SEDANG;
        $durasi->save();

        $service = new CbtService;

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Waktu pengerjaan ujian Anda sudah habis.');

        $service->simpanJawaban($soalSiswa, 'A', false, $siswa);
    }
}
