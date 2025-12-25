<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\LeaveRequest;
use App\Services\AttendanceService;
use App\Services\QrCodeService;
use App\Services\FaceRecognitionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
     * Display attendance page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::now();
        $dayOfWeek = $today->dayOfWeekIso;

        // Get today's schedules
        $todaySchedules = Schedule::with(['subject', 'classroom', 'lessonPeriod'])
            ->where('teacher_id', $user->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('lesson_period_id')
            ->get();

        // Get today's attendances
        $todayAttendances = Attendance::with(['schedule.subject', 'schedule.classroom'])
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->get();

        // Check which schedules have attendance
        $schedulesWithAttendance = $todaySchedules->map(function ($schedule) use ($todayAttendances) {
            $attendance = $todayAttendances->firstWhere('schedule_id', $schedule->id);
            return [
                'schedule' => $schedule,
                'attendance' => $attendance,
                'can_check_in' => !$attendance,
                'can_check_out' => $attendance && !$attendance->time_out,
            ];
        });

        return view('guru.absensi.index', compact('schedulesWithAttendance'));
    }

    /**
     * Check in
     */
    public function checkIn(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'method' => 'required|in:qr_code,face_recognition,gps',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'qr_code' => 'required_if:method,qr_code|string',
            'face_photo' => 'required_if:method,face_recognition|string',
        ]);

        $user = Auth::user();
        $schedule = Schedule::findOrFail($request->schedule_id);

        // Verify schedule belongs to this teacher
        if ($schedule->teacher_id !== $user->id) {
            return back()->with('error', 'Jadwal tidak sesuai dengan guru yang login');
        }

        // Check if already checked in today
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->where('schedule_id', $request->schedule_id)
            ->whereDate('date', Carbon::today())
            ->first();

        if ($existingAttendance) {
            return back()->with('error', 'Anda sudah melakukan check-in untuk jadwal ini');
        }

        try {
            DB::beginTransaction();

            $method = $request->method;
            $qrCodeId = null;
            $faceMatchScore = null;

            // Validate based on method
            if ($method === 'qr_code') {
                $qrValidation = $this->qrCodeService->validateQrCode($request->qr_code);
                if (!$qrValidation['valid']) {
                    return back()->with('error', $qrValidation['message']);
                }
                $qrCodeId = $qrValidation['qr_code_id'] ?? null;
            }

            if ($method === 'face_recognition') {
                $faceValidation = $this->faceService->verifyFace($user, $request->face_photo);
                if (!$faceValidation['match']) {
                    return back()->with('error', 'Verifikasi wajah gagal. Skor: ' . ($faceValidation['score'] ?? 0));
                }
                $faceMatchScore = $faceValidation['score'] ?? null;
            }

            // GPS validation
            $gpsValid = true;
            if ($request->has('latitude') && $request->has('longitude')) {
                $gpsValid = $this->attendanceService->validateGPS(
                    $request->latitude,
                    $request->longitude
                );
            }

            // Create attendance
            $attendance = $this->attendanceService->checkIn([
                'user_id' => $user->id,
                'schedule_id' => $request->schedule_id,
                'method_in' => $method,
                'latitude_in' => $request->latitude,
                'longitude_in' => $request->longitude,
                'qr_code_id' => $qrCodeId,
                'face_photo_in' => $method === 'face_recognition' ? $request->face_photo : null,
                'face_match_score_in' => $faceMatchScore,
                'is_valid_gps_in' => $gpsValid,
            ]);

            DB::commit();

            return back()->with('success', 'Check-in berhasil! Status: ' . $attendance->status);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Check-in gagal: ' . $e->getMessage());
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
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'qr_code' => 'required_if:method,qr_code|string',
            'face_photo' => 'required_if:method,face_recognition|string',
        ]);

        $user = Auth::user();
        $attendance = Attendance::findOrFail($request->attendance_id);

        if ($attendance->user_id !== $user->id) {
            return back()->with('error', 'Unauthorized');
        }

        if ($attendance->time_out) {
            return back()->with('error', 'Anda sudah melakukan check-out');
        }

        try {
            DB::beginTransaction();

            $method = $request->method;
            $faceMatchScore = null;

            // Validate based on method
            if ($method === 'qr_code') {
                $qrValidation = $this->qrCodeService->validateQrCode($request->qr_code);
                if (!$qrValidation['valid']) {
                    return back()->with('error', $qrValidation['message']);
                }
            }

            if ($method === 'face_recognition') {
                $faceValidation = $this->faceService->verifyFace($user, $request->face_photo);
                if (!$faceValidation['match']) {
                    return back()->with('error', 'Verifikasi wajah gagal');
                }
                $faceMatchScore = $faceValidation['score'] ?? null;
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
            $attendance = $this->attendanceService->checkOut($attendance, [
                'method_out' => $method,
                'latitude_out' => $request->latitude,
                'longitude_out' => $request->longitude,
                'face_photo_out' => $method === 'face_recognition' ? $request->face_photo : null,
                'face_match_score_out' => $faceMatchScore,
                'is_valid_gps_out' => $gpsValid,
            ]);

            DB::commit();

            return back()->with('success', 'Check-out berhasil! Durasi: ' . $attendance->duration_minutes . ' menit');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Check-out gagal: ' . $e->getMessage());
        }
    }

    /**
     * Show attendance history
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        
        $query = Attendance::with(['schedule.subject', 'schedule.classroom', 'schedule.lessonPeriod'])
            ->where('user_id', $user->id);

        // Filter by date range if provided
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('time_in', 'desc')
            ->paginate(20);

        return view('guru.absensi.history', compact('attendances'));
    }

    /**
     * Show leave request form
     */
    public function leaveRequest()
    {
        $user = Auth::user();
        
        $leaveRequests = LeaveRequest::where('teacher_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('guru.izin.index', compact('leaveRequests'));
    }

    /**
     * Store leave request
     */
    public function storeLeaveRequest(Request $request)
    {
        $request->validate([
            'leave_date' => 'required|date|after_or_equal:today',
            'reason' => 'required|string|max:500',
            'schedule_ids' => 'required|array',
            'schedule_ids.*' => 'exists:schedules,id',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();

        try {
            DB::beginTransaction();

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('leave-attachments', 'public');
            }

            $leaveRequest = LeaveRequest::create([
                'teacher_id' => $user->id,
                'leave_date' => $request->leave_date,
                'reason' => $request->reason,
                'attachment' => $attachmentPath,
                'status' => 'pending',
            ]);

            // Attach schedules
            $leaveRequest->schedules()->attach($request->schedule_ids);

            DB::commit();

            return back()->with('success', 'Permohonan izin berhasil diajukan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Permohonan izin gagal: ' . $e->getMessage());
        }
    }
}
