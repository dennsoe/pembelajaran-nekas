<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('teacher_id')->constrained('users');
            $table->foreignUlid('subject_id')->constrained('subjects');
            $table->foreignUlid('classroom_id')->constrained('classrooms');
            $table->integer('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignUlid('academic_year_id')->constrained('academic_years');
            $table->integer('semester');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
