<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('group', ['guru', 'kurikulum', 'kepala_sekolah', 'admin', 'superadmin'])->default('guru')->change();
            $table->text('face_encoding')->nullable();
            $table->string('face_photo_path')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('group', ['user', 'admin', 'superadmin'])->change();
            $table->dropColumn(['face_encoding', 'face_photo_path', 'is_active']);
        });
    }
};
