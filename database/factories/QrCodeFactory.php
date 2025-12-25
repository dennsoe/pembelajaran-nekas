<?php

namespace Database\Factories;

use App\Models\QrCode;
use App\Models\Schedule;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class QrCodeFactory extends Factory
{
    protected $model = QrCode::class;

    public function definition(): array
    {
        $expiresAt = now()->addMinutes(5);
        
        return [
            'schedule_id' => Schedule::factory(),
            'student_id' => Student::factory(),
            'code' => Str::random(32),
            'date' => now()->toDateString(),
            'expires_at' => $expiresAt,
            'is_used' => false,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subMinutes(10),
        ]);
    }

    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_used' => true,
        ]);
    }
}
