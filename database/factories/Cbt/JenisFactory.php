<?php

namespace Database\Factories\Cbt;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cbt\Jenis>
 */
class JenisFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_jenis' => $this->faker->words(3, true),
            'kode_jenis' => strtoupper($this->faker->unique()->lexify('???')),
        ];
    }
}
