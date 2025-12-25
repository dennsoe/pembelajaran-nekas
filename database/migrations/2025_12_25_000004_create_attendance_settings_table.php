<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->enum('method', ['qr_only', 'face_only', 'both_optional', 'both_required']);
            $table->integer('tolerance_before')->default(15);
            $table->integer('tolerance_after')->default(15);
            $table->integer('qr_expiry_minutes')->default(5);
            $table->decimal('school_latitude', 10, 7);
            $table->decimal('school_longitude', 10, 7);
            $table->integer('location_radius')->default(100);
            $table->decimal('face_match_threshold', 3, 2)->default(0.6);
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};
