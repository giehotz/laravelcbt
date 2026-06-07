<?php

namespace Database\Factories;

use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

class SemesterFactory extends Factory
{
    protected $model = Semester::class;

    public function definition(): array
    {
        $smt = $this->faker->randomElement(['1', '2']);

        return [
            'smt' => $smt,
            'nama_smt' => $smt === '1' ? 'Ganjil' : 'Genap',
            'active' => false,
        ];
    }
}
