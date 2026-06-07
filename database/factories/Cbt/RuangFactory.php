<?php

namespace Database\Factories\Cbt;

use App\Models\Cbt\Ruang;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ruang>
 */
class RuangFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_ruang' => 'Ruang '.$this->faker->unique()->numberBetween(1, 100),
            'kode_ruang' => 'R'.$this->faker->unique()->numberBetween(1, 100),
        ];
    }
}
