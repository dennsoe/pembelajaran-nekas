<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        $categories = ['wajib', 'peminatan', 'muatan_lokal', 'ekstrakurikuler'];
        
        return [
            'code' => strtoupper($this->faker->unique()->lexify('???')),
            'name' => $this->faker->words(3, true),
            'category' => $this->faker->randomElement($categories),
            'description' => $this->faker->sentence(),
        ];
    }
}
