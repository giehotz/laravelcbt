<?php

namespace Database\Factories\Master;

use App\Models\Master\Kelas;
use App\Models\Master\LevelKelas;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use Illuminate\Database\Eloquent\Factories\Factory;

class KelasFactory extends Factory
{
    protected $model = Kelas::class;

    public function definition(): array
    {
        return [
            'tahun_pelajaran_id' => TahunPelajaran::factory(),
            'semester_id' => Semester::factory(),
            'nama_kelas' => 'Kelas '.$this->faker->unique()->bothify('##-?'),
            'kode_kelas' => strtoupper($this->faker->unique()->bothify('KLS-##?')),
            'jurusan_id' => null,
            'level_id' => LevelKelas::factory(),
            'guru_id' => null,
            'set_siswa' => '0',
        ];
    }
}
