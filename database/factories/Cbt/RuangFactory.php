<?php

namespace Database\Factories\Cbt;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cbt\Ruang>
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
            'nama_ruang' => 'Ruang ' . $this->faker->unique()->numberBetween(1, 100),
            'kode_ruang' => 'R' . $this->faker->unique()->numberBetween(1, 100),
        ];
    }
}
