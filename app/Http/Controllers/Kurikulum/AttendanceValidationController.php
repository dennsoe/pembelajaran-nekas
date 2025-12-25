<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceValidationController extends Controller
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Display attendance validation list
     */
    public function index(Request $request)
    {
        $query = Attendance::with([
            'user',
            'schedule.subject',
            'schedule.classroom',
            'schedule.lessonPeriod'
        ]);

        // Filter by validation status
        if ($request->has('validation_status')) {
            if ($request->validation_status === 'pending') {
                $query->where('is_validated', false);
            } elseif ($request->validation_status === 'validated') {
                $query->where('is_validated', true);
            }
        } else {
            // Default: show pending only
            $query->where('is_validated', false);
        }

        // Filter by date
        if ($request->has('date') && $request->date) {
            $query->whereDate('date', $request->date);
        }

        // Filter by teacher
        if ($request->has('teacher_id') && $request->teacher_id) {
            $query->where('user_id', $request->teacher_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('time_in', 'desc')
            ->paginate(20);

        // Get unique teachers for filter
        $teachers = \App\Models\User::where('group', 'guru')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('kurikulum.validasi.index', compact('attendances', 'teachers'));
    }

    /**
     * Approve attendance
     */
    public function approve(Request $request, Attendance $attendance)
    {
        $user = Auth::user();

        try {
            DB::beginTransaction();

            $attendance->update([
                'is_validated' => true,
                'validated_by' => $user->id,
                'validated_at' => now(),
                'validation_notes' => $request->notes,
            ]);

            // Log activity
            $this->activityLogService->log(
                $user->id,
                'attendance_validation',
                'approve',
                'Attendance',
                $attendance->id,
                "Approved attendance for {$attendance->user->name} on {$attendance->date}"
            );

            DB::commit();

            return back()->with('success', 'Absensi berhasil divalidasi');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Validasi gagal: ' . $e->getMessage());
        }
    }

    /**
     * Reject attendance
     */
    public function reject(Request $request, Attendance $attendance)
    {
        $request->validate([
            'notes' => 'required|string|max:500',
        ]);

        $user = Auth::user();

        try {
            DB::beginTransaction();

            $attendance->update([
                'is_validated' => true,
                'validated_by' => $user->id,
                'validated_at' => now(),
                'validation_notes' => $request->notes,
                'status' => 'invalid', // Mark as invalid
            ]);

            // Log activity
            $this->activityLogService->log(
                $user->id,
                'attendance_validation',
                'reject',
                'Attendance',
                $attendance->id,
                "Rejected attendance for {$attendance->user->name} on {$attendance->date}: {$request->notes}"
            );

            DB::commit();

            return back()->with('success', 'Absensi ditolak dengan catatan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Penolakan gagal: ' . $e->getMessage());
        }
    }
}
