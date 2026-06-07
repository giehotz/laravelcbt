<?php

namespace Database\Factories\Cbt;

use App\Models\Cbt\BankSoal;
use App\Models\Cbt\Jenis;
use App\Models\Master\Guru;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankSoalFactory extends Factory
{
    protected $model = BankSoal::class;

    public function definition(): array
    {
        return [
            'jenis_id' => Jenis::factory(),
            'kode' => strtoupper($this->faker->unique()->bothify('BS-##?')),
            'level' => '10',
            'kelas' => [],
            'mapel_id' => null,
            'jurusan_id' => null,
            'guru_id' => Guru::factory(),
            'tahun_pelajaran_id' => TahunPelajaran::factory(),
            'semester_id' => Semester::factory(),
            'nama' => $this->faker->sentence(3),
            'kkm' => 75,
            'jml_pg' => 0,
            'tampil_pg' => 10,
            'bobot_pg' => 10,
            'jml_esai' => 0,
            'tampil_esai' => 0,
            'bobot_esai' => 0,
            'jml_kompleks' => 0,
            'tampil_kompleks' => 0,
            'bobot_kompleks' => 0,
            'skoring_kompleks' => 'all_or_nothing',
            'jml_jodohkan' => 0,
            'tampil_jodohkan' => 0,
            'bobot_jodohkan' => 0,
            'jml_isian' => 0,
            'tampil_isian' => 0,
            'bobot_isian' => 0,
            'opsi' => 5,
            'status' => 1,
            'status_soal' => 1,
            'soal_agama' => '-',
        ];
    }
}
