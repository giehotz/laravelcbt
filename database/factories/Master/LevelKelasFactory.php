<?php

namespace Database\Factories\Master;

use App\Models\Master\LevelKelas;
use Illuminate\Database\Eloquent\Factories\Factory;

class LevelKelasFactory extends Factory
{
    protected $model = LevelKelas::class;

    public function definition(): array
    {
        return [
            'level' => $this->faker->randomElement(['VII', 'VIII', 'IX', 'X', 'XI', 'XII', '1', '2', '3']),
        ];
    }
}
