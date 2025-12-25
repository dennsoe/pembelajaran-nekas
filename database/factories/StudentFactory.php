<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'nis' => $this->faker->unique()->numerify('##########'),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'classroom_id' => Classroom::factory(),
            'is_class_leader' => false,
            'phone' => $this->faker->phoneNumber(),
        ];
    }

    public function classLeader(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_class_leader' => true,
        ]);
    }
}
