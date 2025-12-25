<?php

namespace Database\Factories;

use App\Models\Schedule;
use App\Models\User;
use App\Models\Subject;
use App\Models\Classroom;
use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        $startHour = $this->faker->numberBetween(7, 14);
        $startTime = sprintf('%02d:00:00', $startHour);
        $endTime = sprintf('%02d:00:00', $startHour + 1);
        
        return [
            'teacher_id' => User::factory(),
            'subject_id' => Subject::factory(),
            'classroom_id' => Classroom::factory(),
            'day_of_week' => $this->faker->numberBetween(1, 5),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'academic_year_id' => AcademicYear::factory(),
            'semester' => $this->faker->numberBetween(1, 2),
            'is_active' => true,
        ];
    }
}
