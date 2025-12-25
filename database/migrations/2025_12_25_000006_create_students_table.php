<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('nis')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->foreignUlid('classroom_id')->constrained('classrooms');
            $table->boolean('is_class_leader')->default(false);
            $table->string('phone')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
