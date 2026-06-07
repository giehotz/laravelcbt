<?php

namespace Database\Factories\Master;

use App\Models\Master\Guru;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuruFactory extends Factory
{
    protected $model = Guru::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nama_guru' => $this->faker->name(),
            'kode_guru' => strtoupper($this->faker->unique()->lexify('???')),
            'nip' => $this->faker->unique()->numerify('##################'),
            'email' => $this->faker->unique()->safeEmail(),
            'status_aktif' => 'Aktif',
        ];
    }
}
