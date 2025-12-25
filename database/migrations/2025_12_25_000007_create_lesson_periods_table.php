<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lesson_periods', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->integer('period_number');
            $table->integer('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration_minutes');
            $table->foreignUlid('academic_year_id')->constrained('academic_years');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_periods');
    }
};
