<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MataPelajaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MataPelajaran>
 */
class MataPelajaranFactory extends Factory
{
    protected $model = MataPelajaran::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_mapel' => strtoupper(fake()->unique()->bothify('MP-###')),
            'nama_mapel' => fake()->words(2, true),
            'jenjang' => 'MTs',
            'kkm' => fake()->numberBetween(70, 78),
            'deskripsi' => fake()->sentence(),
        ];
    }
}
