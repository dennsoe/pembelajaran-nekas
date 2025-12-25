<?php

namespace App\Services;

use App\Models\QrCode;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\AttendanceSetting;
use Illuminate\Support\Str;
use Carbon\Carbon;

class QrCodeService
{
    /**
     * Generate QR Code untuk schedule tertentu
     */
    public function generateQrCode(Schedule $schedule, Student $student): QrCode
    {
        $settings = AttendanceSetting::first();
        $expiryMinutes = $settings->qr_expiry_minutes ?? 5;

        // Invalidate QR codes lama untuk schedule yang sama hari ini
        QrCode::where('schedule_id', $schedule->id)
            ->where('date', now()->toDateString())
            ->where('is_used', false)
            ->update(['is_used' => true]);

        // Generate QR code baru
        $qrCode = QrCode::create([
            'schedule_id' => $schedule->id,
            'student_id' => $student->id,
            'code' => Str::random(32),
            'date' => now()->toDateString(),
            'expires_at' => now()->addMinutes($expiryMinutes),
            'is_used' => false,
        ]);

        return $qrCode;
    }

    /**
     * Validasi QR Code
     */
    public function validateQrCode(string $code): array
    {
        $qrCode = QrCode::where('code', $code)->first();

        if (!$qrCode) {
            return [
                'valid' => false,
                'message' => 'QR Code tidak ditemukan',
            ];
        }

        if ($qrCode->is_used) {
            return [
                'valid' => false,
                'message' => 'QR Code sudah digunakan',
            ];
        }

        if ($qrCode->isExpired()) {
            return [
                'valid' => false,
                'message' => 'QR Code sudah kadaluarsa',
            ];
        }

        if ($qrCode->date != now()->toDateString()) {
            return [
                'valid' => false,
                'message' => 'QR Code tidak valid untuk hari ini',
            ];
        }

        return [
            'valid' => true,
            'qr_code' => $qrCode,
            'schedule' => $qrCode->schedule,
        ];
    }

    /**
     * Mark QR Code sebagai sudah digunakan
     */
    public function markAsUsed(QrCode $qrCode): void
    {
        $qrCode->update(['is_used' => true]);
    }

    /**
     * Check apakah schedule aktif dan bisa generate QR
     */
    public function canGenerateQr(Schedule $schedule): array
    {
        $now = now();
        $dayOfWeek = $now->dayOfWeek == 0 ? 7 : $now->dayOfWeek; // Minggu = 7

        if ($schedule->day_of_week != $dayOfWeek) {
            return [
                'can_generate' => false,
                'message' => 'Jadwal tidak aktif untuk hari ini',
            ];
        }

        $settings = AttendanceSetting::first();
        $toleranceBefore = $settings->tolerance_before ?? 15;

        $scheduleTime = Carbon::parse($schedule->start_time);
        $currentTime = $now->format('H:i:s');
        $allowedStartTime = $scheduleTime->copy()->subMinutes($toleranceBefore)->format('H:i:s');

        if ($currentTime < $allowedStartTime) {
            return [
                'can_generate' => false,
                'message' => 'Belum waktunya generate QR Code. Mulai ' . $allowedStartTime,
            ];
        }

        $scheduleEndTime = Carbon::parse($schedule->end_time);
        if ($currentTime > $scheduleEndTime->format('H:i:s')) {
            return [
                'can_generate' => false,
                'message' => 'Jadwal sudah berakhir',
            ];
        }

        return [
            'can_generate' => true,
            'message' => 'QR Code dapat di-generate',
        ];
    }

    /**
     * Get active QR code untuk schedule hari ini
     */
    public function getActiveQrCode(Schedule $schedule): ?QrCode
    {
        return QrCode::where('schedule_id', $schedule->id)
            ->where('date', now()->toDateString())
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();
    }
}
