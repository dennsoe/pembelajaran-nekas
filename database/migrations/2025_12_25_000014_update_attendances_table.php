<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignUlid('schedule_id')->nullable()->after('user_id')->constrained('schedules');
            $table->foreignUlid('qr_code_id')->nullable()->after('schedule_id')->constrained('qr_codes');
            $table->enum('method_in', ['qr_code', 'face_recognition'])->nullable()->after('time_in');
            $table->enum('method_out', ['qr_code', 'face_recognition'])->nullable()->after('time_out');
            $table->decimal('latitude_in', 10, 7)->nullable()->after('method_in');
            $table->decimal('longitude_in', 10, 7)->nullable()->after('latitude_in');
            $table->decimal('latitude_out', 10, 7)->nullable()->after('method_out');
            $table->decimal('longitude_out', 10, 7)->nullable()->after('latitude_out');
            $table->string('face_photo_in')->nullable()->after('latitude_in');
            $table->string('face_photo_out')->nullable()->after('latitude_out');
            $table->decimal('face_match_score_in', 4, 2)->nullable()->after('face_photo_in');
            $table->decimal('face_match_score_out', 4, 2)->nullable()->after('face_photo_out');
            $table->foreignUlid('validated_by')->nullable()->after('status')->constrained('users');
            $table->timestamp('validated_at')->nullable()->after('validated_by');
            $table->enum('validation_status', ['pending', 'validated', 'rejected'])->default('pending')->after('validated_at');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->dropForeign(['qr_code_id']);
            $table->dropForeign(['validated_by']);
            $table->dropColumn([
                'schedule_id', 'qr_code_id', 'method_in', 'method_out',
                'latitude_in', 'longitude_in', 'latitude_out', 'longitude_out',
                'face_photo_in', 'face_photo_out', 'face_match_score_in', 'face_match_score_out',
                'validated_by', 'validated_at', 'validation_status'
            ]);
        });
    }
};
