<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\User;
use App\Models\QrCode;
use App\Models\AttendanceSetting;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Record attendance masuk
     */
    public function recordCheckIn(
        User $teacher,
        Schedule $schedule,
        string $method,
        ?QrCode $qrCode = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $facePhoto = null,
        ?float $faceMatchScore = null
    ): array {
        // Check apakah sudah absen hari ini untuk schedule ini
        $existingAttendance = Attendance::where('user_id', $teacher->id)
            ->where('schedule_id', $schedule->id)
            ->where('date', now()->toDateString())
            ->first();

        if ($existingAttendance && $existingAttendance->time_in) {
            return [
                'success' => false,
                'message' => 'Anda sudah absen masuk untuk jadwal ini',
            ];
        }

        // Validasi lokasi
        if ($latitude && $longitude) {
            $locationValid = $this->validateLocation($latitude, $longitude);
            if (!$locationValid['valid']) {
                return [
                    'success' => false,
                    'message' => $locationValid['message'],
                ];
            }
        }

        // Tentukan status berdasarkan waktu
        $status = $this->determineStatus($schedule->start_time);

        // Create or update attendance
        if ($existingAttendance) {
            $existingAttendance->update([
                'time_in' => now()->format('H:i:s'),
                'method_in' => $method,
                'latitude_in' => $latitude,
                'longitude_in' => $longitude,
                'face_photo_in' => $facePhoto,
                'face_match_score_in' => $faceMatchScore,
                'qr_code_id' => $qrCode?->id,
                'status' => $status,
            ]);
            $attendance = $existingAttendance;
        } else {
            $attendance = Attendance::create([
                'user_id' => $teacher->id,
                'schedule_id' => $schedule->id,
                'qr_code_id' => $qrCode?->id,
                'date' => now()->toDateString(),
                'time_in' => now()->format('H:i:s'),
                'method_in' => $method,
                'latitude_in' => $latitude,
                'longitude_in' => $longitude,
                'face_photo_in' => $facePhoto,
                'face_match_score_in' => $faceMatchScore,
                'status' => $status,
                'validation_status' => 'pending',
            ]);
        }

        // Mark QR code as used
        if ($qrCode) {
            $qrCode->update(['is_used' => true]);
        }

        return [
            'success' => true,
            'message' => 'Absen masuk berhasil',
            'attendance' => $attendance,
            'status' => $status,
        ];
    }

    /**
     * Record attendance keluar
     */
    public function recordCheckOut(
        Attendance $attendance,
        string $method,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $facePhoto = null,
        ?float $faceMatchScore = null
    ): array {
        if ($attendance->time_out) {
            return [
                'success' => false,
                'message' => 'Anda sudah absen keluar untuk jadwal ini',
            ];
        }

        // Validasi lokasi
        if ($latitude && $longitude) {
            $locationValid = $this->validateLocation($latitude, $longitude);
            if (!$locationValid['valid']) {
                return [
                    'success' => false,
                    'message' => $locationValid['message'],
                ];
            }
        }

        $attendance->update([
            'time_out' => now()->format('H:i:s'),
            'method_out' => $method,
            'latitude_out' => $latitude,
            'longitude_out' => $longitude,
            'face_photo_out' => $facePhoto,
            'face_match_score_out' => $faceMatchScore,
        ]);

        return [
            'success' => true,
            'message' => 'Absen keluar berhasil',
            'attendance' => $attendance,
        ];
    }

    /**
     * Validate lokasi GPS
     */
    public function validateLocation(float $latitude, float $longitude): array
    {
        $settings = AttendanceSetting::first();
        
        if (!$settings) {
            return ['valid' => true]; // Jika tidak ada setting, skip validasi
        }

        $schoolLat = $settings->school_latitude;
        $schoolLon = $settings->school_longitude;
        $radius = $settings->location_radius;

        $distance = $this->calculateDistance($latitude, $longitude, $schoolLat, $schoolLon);

        if ($distance > $radius) {
            return [
                'valid' => false,
                'message' => "Anda berada di luar radius sekolah ({$distance}m dari sekolah, max: {$radius}m)",
            ];
        }

        return [
            'valid' => true,
            'distance' => $distance,
        ];
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meter

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * $earthRadius);
    }

    /**
     * Determine attendance status based on time
     */
    private function determineStatus(string $scheduleStartTime): string
    {
        $settings = AttendanceSetting::first();
        $toleranceAfter = $settings->tolerance_after ?? 15;

        $scheduleTime = Carbon::parse($scheduleStartTime);
        $currentTime = Carbon::now();
        $lateThreshold = $scheduleTime->copy()->addMinutes($toleranceAfter);

        if ($currentTime->greaterThan($lateThreshold)) {
            return 'late';
        }

        return 'present';
    }

    /**
     * Get attendance untuk guru dan schedule tertentu
     */
    public function getAttendance(User $teacher, Schedule $schedule, ?string $date = null): ?Attendance
    {
        $date = $date ?? now()->toDateString();
        
        return Attendance::where('user_id', $teacher->id)
            ->where('schedule_id', $schedule->id)
            ->where('date', $date)
            ->first();
    }

    /**
     * Validate attendance (untuk kurikulum/kepala sekolah)
     */
    public function validateAttendance(Attendance $attendance, User $validator, string $status, ?string $notes = null): array
    {
        if (!in_array($validator->group, ['kurikulum', 'kepala_sekolah', 'admin', 'superadmin'])) {
            return [
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk memvalidasi absensi',
            ];
        }

        $attendance->update([
            'validated_by' => $validator->id,
            'validated_at' => now(),
            'validation_status' => $status,
            'note' => $notes,
        ]);

        return [
            'success' => true,
            'message' => 'Validasi absensi berhasil',
            'attendance' => $attendance,
        ];
    }
}
