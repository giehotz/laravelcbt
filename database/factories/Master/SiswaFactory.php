<?php

namespace Database\Factories\Master;

use App\Models\Master\Siswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiswaFactory extends Factory
{
    protected $model = Siswa::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nisn' => $this->faker->unique()->numerify('##########'),
            'nis' => $this->faker->unique()->numerify('#####'),
            'nama' => $this->faker->name(),
            'jenis_kelamin' => $this->faker->randomElement(['L', 'P']),
            'warga_negara' => 'WNI',
        ];
    }
}
