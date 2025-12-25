<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\AttendanceService;
use App\Services\QrCodeService;
use App\Services\FaceRecognitionService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected $attendanceService;
    protected $qrCodeService;
    protected $faceService;

    public function __construct(
        AttendanceService $attendanceService,
        QrCodeService $qrCodeService,
        FaceRecognitionService $faceService
    ) {
        $this->attendanceService = $attendanceService;
        $this->qrCodeService = $qrCodeService;
        $this->faceService = $faceService;
    }

    /**
     * Check in
     */
    public function checkIn(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'method' => 'required|in:qr_code,face_recognition,gps',
            'latitude' => 'required_if:method,gps|numeric',
            'longitude' => 'required_if:method,gps|numeric',
            'qr_code' => 'required_if:method,qr_code|string',
            'face_photo' => 'required_if:method,face_recognition|string', // base64
        ]);

        $user = $request->user();
        $method = $request->method;

        // Validate based on method
        if ($method === 'qr_code') {
            $qrValidation = $this->qrCodeService->validateQrCode($request->qr_code);
            if (!$qrValidation['valid']) {
                return response()->json([
                    'message' => $qrValidation['message'],
                ], 422);
            }
        }

        if ($method === 'face_recognition') {
            $faceValidation = $this->faceService->verifyFace(
                $user,
                $request->face_photo
            );
            if (!$faceValidation['match']) {
                return response()->json([
                    'message' => 'Face verification failed',
                    'match_score' => $faceValidation['score'] ?? 0,
                ], 422);
            }
        }

        // GPS validation (if provided)
        $gpsValid = true;
        if ($request->has('latitude') && $request->has('longitude')) {
            $gpsValid = $this->attendanceService->validateGPS(
                $request->latitude,
                $request->longitude
            );
        }

        // Create attendance record
        try {
            $attendance = $this->attendanceService->checkIn([
                'user_id' => $user->id,
                'schedule_id' => $request->schedule_id,
                'method_in' => $method,
                'latitude_in' => $request->latitude,
                'longitude_in' => $request->longitude,
                'qr_code_id' => $method === 'qr_code' ? $qrValidation['qr_code_id'] ?? null : null,
                'face_photo_in' => $method === 'face_recognition' ? $request->face_photo : null,
                'face_match_score_in' => $method === 'face_recognition' ? $faceValidation['score'] ?? null : null,
                'is_valid_gps_in' => $gpsValid,
            ]);

            return response()->json([
                'message' => 'Check-in successful',
                'data' => [
                    'id' => $attendance->id,
                    'time' => $attendance->time_in,
                    'method' => $attendance->method_in,
                    'is_valid_gps' => $attendance->is_valid_gps_in,
                    'status' => $attendance->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Check-in failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check out
     */
    public function checkOut(Request $request)
    {
        $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
            'method' => 'required|in:qr_code,face_recognition,gps',
            'latitude' => 'required_if:method,gps|numeric',
            'longitude' => 'required_if:method,gps|numeric',
            'qr_code' => 'required_if:method,qr_code|string',
            'face_photo' => 'required_if:method,face_recognition|string',
        ]);

        $user = $request->user();
        $attendance = Attendance::find($request->attendance_id);

        if ($attendance->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($attendance->time_out) {
            return response()->json([
                'message' => 'Already checked out',
            ], 422);
        }

        $method = $request->method;

        // Validate based on method
        if ($method === 'qr_code') {
            $qrValidation = $this->qrCodeService->validateQrCode($request->qr_code);
            if (!$qrValidation['valid']) {
                return response()->json([
                    'message' => $qrValidation['message'],
                ], 422);
            }
        }

        if ($method === 'face_recognition') {
            $faceValidation = $this->faceService->verifyFace(
                $user,
                $request->face_photo
            );
            if (!$faceValidation['match']) {
                return response()->json([
                    'message' => 'Face verification failed',
                ], 422);
            }
        }

        // GPS validation
        $gpsValid = true;
        if ($request->has('latitude') && $request->has('longitude')) {
            $gpsValid = $this->attendanceService->validateGPS(
                $request->latitude,
                $request->longitude
            );
        }

        // Update attendance
        try {
            $attendance = $this->attendanceService->checkOut($attendance, [
                'method_out' => $method,
                'latitude_out' => $request->latitude,
                'longitude_out' => $request->longitude,
                'face_photo_out' => $method === 'face_recognition' ? $request->face_photo : null,
                'face_match_score_out' => $method === 'face_recognition' ? $faceValidation['score'] ?? null : null,
                'is_valid_gps_out' => $gpsValid,
            ]);

            return response()->json([
                'message' => 'Check-out successful',
                'data' => [
                    'id' => $attendance->id,
                    'time_in' => $attendance->time_in,
                    'time_out' => $attendance->time_out,
                    'duration_minutes' => $attendance->duration_minutes,
                    'status' => $attendance->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Check-out failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get attendance history
     */
    public function history(Request $request)
    {
        $user = $request->user();
        
        $attendances = Attendance::with(['schedule.subject', 'schedule.classroom'])
            ->where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->orderBy('time_in', 'desc')
            ->paginate(20);

        return response()->json([
            'message' => 'Attendance history retrieved successfully',
            'data' => $attendances->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'date' => $attendance->date,
                    'time_in' => $attendance->time_in,
                    'time_out' => $attendance->time_out,
                    'duration_minutes' => $attendance->duration_minutes,
                    'status' => $attendance->status,
                    'method_in' => $attendance->method_in,
                    'method_out' => $attendance->method_out,
                    'subject' => $attendance->schedule->subject->name ?? null,
                    'classroom' => $attendance->schedule->classroom->name ?? null,
                    'is_validated' => $attendance->is_validated,
                ];
            }),
            'pagination' => [
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
                'per_page' => $attendances->perPage(),
                'total' => $attendances->total(),
            ],
        ]);
    }

    /**
     * Get today's attendance
     */
    public function today(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        $attendances = Attendance::with(['schedule.subject', 'schedule.classroom', 'schedule.lessonPeriod'])
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->orderBy('time_in')
            ->get();

        return response()->json([
            'message' => 'Today\'s attendance retrieved successfully',
            'date' => $today->format('Y-m-d'),
            'data' => $attendances->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'time_in' => $attendance->time_in,
                    'time_out' => $attendance->time_out,
                    'status' => $attendance->status,
                    'subject' => $attendance->schedule->subject->name ?? null,
                    'classroom' => $attendance->schedule->classroom->name ?? null,
                    'lesson_period' => [
                        'start_time' => $attendance->schedule->lessonPeriod->start_time ?? null,
                        'end_time' => $attendance->schedule->lessonPeriod->end_time ?? null,
                    ],
                ];
            }),
        ]);
    }

    /**
     * Validate QR Code
     */
    public function validateQrCode(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $result = $this->qrCodeService->validateQrCode($request->qr_code);

        return response()->json($result);
    }

    /**
     * Register face encoding
     */
    public function registerFace(Request $request)
    {
        $request->validate([
            'face_photo' => 'required|string', // base64
        ]);

        $user = $request->user();

        try {
            $result = $this->faceService->storeFaceEncoding($user, $request->face_photo);

            return response()->json([
                'message' => 'Face registered successfully',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Face registration failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify face
     */
    public function verifyFace(Request $request)
    {
        $request->validate([
            'face_photo' => 'required|string', // base64
        ]);

        $user = $request->user();

        try {
            $result = $this->faceService->verifyFace($user, $request->face_photo);

            return response()->json([
                'message' => $result['match'] ? 'Face verified successfully' : 'Face verification failed',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Face verification failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
