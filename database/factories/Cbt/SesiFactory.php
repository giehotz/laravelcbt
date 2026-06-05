<?php

namespace Database\Factories\Cbt;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cbt\Sesi>
 */
class SesiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_sesi' => 'Sesi ' . $this->faker->unique()->numberBetween(1, 100),
            'kode_sesi' => 'S' . $this->faker->unique()->numberBetween(1, 100),
            'waktu_mulai' => '07:30:00',
            'waktu_akhir' => '09:30:00',
            'aktif' => true,
        ];
    }
}
