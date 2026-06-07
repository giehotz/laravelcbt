<?php

namespace Database\Seeders;

use App\Models\Cbt\BankSoal;
use App\Models\Cbt\Jadwal;
use App\Models\Cbt\SoalPair;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankSoalFikihSeeder extends Seeder
{
    public function run(): void
    {
        $bank = BankSoal::firstOrCreate(
            ['kode' => 'UM-FQ-2526'],
            [
                'jenis_id' => 1,
                'kode' => 'UM-FQ-2526',
                'level' => '4',
                'kelas' => [2],
                'mapel_id' => 3,
                'jurusan_id' => null,
                'guru_id' => 1,
                'tahun_pelajaran_id' => 1,
                'semester_id' => 2,
                'nama' => 'ASAS 25-26 - Fikih Kelas 4',
                'kkm' => 70,
                'tampil_pg' => 10,
                'bobot_pg' => 50,
                'tampil_kompleks' => 5,
                'bobot_kompleks' => 30,
                'skoring_kompleks' => 'partial',
                'tampil_jodohkan' => 2,
                'bobot_jodohkan' => 20,
                'opsi' => 4,
                'deskripsi' => 'Ujian Madrasah ASAS 25-26 Mata Pelajaran Fikih Kelas 4',
                'status' => 1,
                'status_soal' => 1,
            ]);

        $bank->skipSyncJumlah = true;

        // Buat jadwal agar ujian tampil di siswa (jalankan selalu jika belum ada)
        Jadwal::firstOrCreate(
            ['bank_id' => $bank->id],
            [
                'tahun_pelajaran_id' => 1,
                'semester_id' => 2,
                'bank_id' => $bank->id,
                'jenis_id' => 1,
                'tgl_mulai' => '2026-06-01 00:00:00',
                'tgl_selesai' => '2026-06-30 23:59:59',
                'durasi_ujian' => 90,
                'status' => 1,
            ]
        );

        // Lewati pembuatan soal jika bank sudah ada
        if (! $bank->wasRecentlyCreated) {
            $bank->skipSyncJumlah = false;
            $this->command?->info('Bank soal Fikih sudah ada, dilewati.');

            return;
        }

        $pg = [
            [
                'soal' => 'Rukun Islam yang pertama adalah…',
                'opsi_a' => 'Puasa Ramadan',
                'opsi_b' => 'Membaca dua kalimat syahadat',
                'opsi_c' => 'Salat lima waktu',
                'opsi_d' => 'Membayar zakat',
                'jawaban' => 'B',
            ],
            [
                'soal' => 'Salat wajib yang dikerjakan pada waktu fajar atau pagi buta adalah…',
                'opsi_a' => 'Subuh',
                'opsi_b' => 'Zuhur',
                'opsi_c' => 'Asar',
                'opsi_d' => 'Magrib',
                'jawaban' => 'A',
            ],
            [
                'soal' => 'Rukun wudu yang pertama adalah…',
                'opsi_a' => 'Membasuh kaki',
                'opsi_b' => 'Membasuh muka',
                'opsi_c' => 'Niat',
                'opsi_d' => 'Membasuh tangan',
                'jawaban' => 'C',
            ],
            [
                'soal' => 'Hukum melaksanakan salat lima waktu bagi muslim yang balig dan berakal adalah…',
                'opsi_a' => 'Sunah',
                'opsi_b' => 'Mubah',
                'opsi_c' => 'Wajib',
                'opsi_d' => 'Makruh',
                'jawaban' => 'C',
            ],
            [
                'soal' => 'Bersuci dari hadas kecil dilakukan dengan…',
                'opsi_a' => 'Tayamum',
                'opsi_b' => 'Mandi wajib',
                'opsi_c' => 'Wudu',
                'opsi_d' => 'Istinja',
                'jawaban' => 'C',
            ],
            [
                'soal' => 'Fardu kifayah artinya adalah kewajiban yang…',
                'opsi_a' => 'Wajib dilakukan setiap individu',
                'opsi_b' => 'Gugur jika sudah ada yang melaksanakan',
                'opsi_c' => 'Tidak wajib dilakukan',
                'opsi_d' => 'Hanya dilakukan oleh pemimpin',
                'jawaban' => 'B',
            ],
            [
                'soal' => 'Air yang suci dan menyucikan disebut air…',
                'opsi_a' => 'Mutlak',
                'opsi_b' => 'Mustamal',
                'opsi_c' => 'Mutannajis',
                'opsi_d' => 'Musyammas',
                'jawaban' => 'A',
            ],
            [
                'soal' => 'Salat sunah yang dikerjakan pada malam hari bulan Ramadan adalah…',
                'opsi_a' => 'Salat Duha',
                'opsi_b' => 'Salat Tahajud',
                'opsi_c' => 'Salat Tarawih',
                'opsi_d' => 'Salat Witir',
                'jawaban' => 'C',
            ],
            [
                'soal' => 'Zakat fitrah wajib dikeluarkan pada bulan…',
                'opsi_a' => 'Syawal',
                'opsi_b' => 'Ramadan',
                'opsi_c' => 'Zulhijah',
                'opsi_d' => 'Muharam',
                'jawaban' => 'B',
            ],
            [
                'soal' => 'Najis yang dapat dimaafkan (tidak wajib dibersihkan) disebut…',
                'opsi_a' => 'Najis mukhaffafah',
                'opsi_b' => 'Najis mutawassitah',
                'opsi_c' => 'Najis mughallazah',
                'opsi_d' => 'Najis makruh',
                'jawaban' => 'A',
            ],
        ];

        $pgIds = [];
        foreach ($pg as $i => $item) {
            $pgIds[] = DB::table('cbt_soal')->insertGetId([
                'bank_id' => $bank->id,
                'mapel_id' => 3,
                'jenis' => 1,
                'nomor_soal' => $i + 1,
                'soal' => $item['soal'],
                'opsi_a' => $item['opsi_a'],
                'opsi_b' => $item['opsi_b'],
                'opsi_c' => $item['opsi_c'],
                'opsi_d' => $item['opsi_d'],
                'jawaban' => $item['jawaban'],
                'tampilkan' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $kompleks = [
            [
                'soal' => 'Berikut ini yang termasuk rukun salat adalah…',
                'opsi_a' => 'Takbiratulihram',
                'opsi_b' => 'Membaca niat',
                'opsi_c' => 'Makan sebelum salat',
                'opsi_d' => 'Salam',
                'jawaban' => json_encode(['A', 'B', 'D']),
            ],
            [
                'soal' => 'Yang termasuk syarat sah salat adalah…',
                'opsi_a' => 'Suci dari hadas dan najis',
                'opsi_b' => 'Berakal sehat',
                'opsi_c' => 'Kaya raya',
                'opsi_d' => 'Menutup aurat',
                'jawaban' => json_encode(['A', 'B', 'D']),
            ],
            [
                'soal' => 'Yang membatalkan wudu adalah…',
                'opsi_a' => 'Buang air kecil',
                'opsi_b' => 'Tidur pulas',
                'opsi_c' => 'Makan',
                'opsi_d' => 'Buang angin',
                'jawaban' => json_encode(['A', 'B', 'D']),
            ],
            [
                'soal' => 'Hikmah berpuasa Ramadan antara lain…',
                'opsi_a' => 'Melatih kesabaran',
                'opsi_b' => 'Menumbuhkan rasa empati',
                'opsi_c' => 'Membuat tubuh menjadi lemah',
                'opsi_d' => 'Mendapat pahala dari Allah',
                'jawaban' => json_encode(['A', 'B', 'D']),
            ],
            [
                'soal' => 'Yang termasuk salat sunah rawatib adalah…',
                'opsi_a' => 'Salat Duha',
                'opsi_b' => 'Salat sunah sebelum Subuh',
                'opsi_c' => 'Salat Witir',
                'opsi_d' => 'Salat sunah sebelum Zuhur',
                'jawaban' => json_encode(['B', 'D']),
            ],
        ];

        foreach ($kompleks as $i => $item) {
            DB::table('cbt_soal')->insertGetId([
                'bank_id' => $bank->id,
                'mapel_id' => 3,
                'jenis' => 2,
                'nomor_soal' => $i + 1,
                'soal' => $item['soal'],
                'opsi_a' => $item['opsi_a'],
                'opsi_b' => $item['opsi_b'],
                'opsi_c' => $item['opsi_c'],
                'opsi_d' => $item['opsi_d'],
                'jawaban' => $item['jawaban'],
                'tampilkan' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $jodohkan = [
            [
                'soal' => 'Pasangkan ibadah berikut dengan waktunya yang tepat!',
                'pairs' => [
                    ['kiri' => 'Salat Subuh', 'kanan' => '2 rakaat'],
                    ['kiri' => 'Salat Zuhur', 'kanan' => '4 rakaat'],
                    ['kiri' => 'Salat Magrib', 'kanan' => '3 rakaat'],
                ],
            ],
            [
                'soal' => 'Pasangkan jenis najis berikut dengan cara membersihkannya!',
                'pairs' => [
                    ['kiri' => 'Najis mukhaffafah', 'kanan' => 'Percikan air'],
                    ['kiri' => 'Najis mughallazah', 'kanan' => 'Tanah + air'],
                    ['kiri' => 'Najis mutawassitah', 'kanan' => 'Dibilas dengan air'],
                ],
            ],
        ];

        foreach ($jodohkan as $i => $item) {
            $soalId = DB::table('cbt_soal')->insertGetId([
                'bank_id' => $bank->id,
                'mapel_id' => 3,
                'jenis' => 3,
                'nomor_soal' => $i + 1,
                'soal' => $item['soal'],
                'tampilkan' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($item['pairs'] as $pair) {
                SoalPair::create([
                    'soal_id' => $soalId,
                    'kiri' => $pair['kiri'],
                    'kanan' => $pair['kanan'],
                ]);
            }
        }

        $bank->skipSyncJumlah = false;
        $bank->syncJumlahSoal();
    }
}
