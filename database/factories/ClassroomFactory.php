<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassroomFactory extends Factory
{
    protected $model = Classroom::class;

    public function definition(): array
    {
        $grades = [10, 11, 12];
        $sections = ['A', 'B', 'C', 'D'];
        
        return [
            'name' => 'X-' . $this->faker->randomElement($sections),
            'grade' => $this->faker->randomElement($grades),
            'academic_year_id' => AcademicYear::factory(),
        ];
    }
}
