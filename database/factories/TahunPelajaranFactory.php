<?php

namespace Database\Factories;

use App\Models\TahunPelajaran;
use Illuminate\Database\Eloquent\Factories\Factory;

class TahunPelajaranFactory extends Factory
{
    protected $model = TahunPelajaran::class;

    public function definition(): array
    {
        $year = $this->faker->unique()->numberBetween(2020, 2030);

        return [
            'tahun' => $year.'/'.($year + 1),
            'active' => false,
        ];
    }
}
