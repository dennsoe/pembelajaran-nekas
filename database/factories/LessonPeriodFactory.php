<?php

namespace Database\Factories;

use App\Models\LessonPeriod;
use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

class LessonPeriodFactory extends Factory
{
    protected $model = LessonPeriod::class;

    public function definition(): array
    {
        $period = $this->faker->numberBetween(1, 8);
        $startHour = 7 + ($period - 1);
        $duration = $this->faker->randomElement([35, 45]);
        
        return [
            'period_number' => $period,
            'day_of_week' => $this->faker->numberBetween(1, 5),
            'start_time' => sprintf('%02d:00:00', $startHour),
            'end_time' => sprintf('%02d:%02d:00', $startHour, $duration),
            'duration_minutes' => $duration,
            'academic_year_id' => AcademicYear::factory(),
            'is_active' => true,
        ];
    }
}
