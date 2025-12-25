<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('substitute_teachers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('leave_request_id')->constrained('leave_requests');
            $table->foreignUlid('original_teacher_id')->constrained('users');
            $table->foreignUlid('substitute_teacher_id')->constrained('users');
            $table->foreignUlid('schedule_id')->constrained('schedules');
            $table->date('date');
            $table->foreignUlid('assigned_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('substitute_teachers');
    }
};
