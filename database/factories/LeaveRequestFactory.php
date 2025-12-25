<?php

namespace Database\Factories;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $types = ['sick', 'permission', 'other'];
        $statuses = ['pending', 'approved', 'rejected'];
        
        return [
            'teacher_id' => User::factory(),
            'schedule_id' => Schedule::factory(),
            'date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'type' => $this->faker->randomElement($types),
            'reason' => $this->faker->sentence(),
            'attachment' => null,
            'status' => $this->faker->randomElement($statuses),
            'approved_by' => null,
            'approved_at' => null,
            'notes' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }
}
