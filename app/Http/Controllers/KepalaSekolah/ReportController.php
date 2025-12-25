<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Classroom;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display report page
     */
    public function index(Request $request)
    {
        return view('kepala-sekolah.laporan.index');
    }

    /**
     * Teacher attendance report
     */
    public function teacherAttendance(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'teacher_id' => 'nullable|exists:users,id',
        ]);

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

        $query = Attendance::with(['user', 'schedule.subject', 'schedule.classroom'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($request->teacher_id) {
            $query->where('user_id', $request->teacher_id);
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('time_in', 'desc')
            ->get();

        // Group by teacher
        $teacherAttendances = $attendances->groupBy('user_id')->map(function ($userAttendances, $userId) {
            $user = $userAttendances->first()->user;
            return [
                'teacher' => $user,
                'total' => $userAttendances->count(),
                'on_time' => $userAttendances->where('status', 'on_time')->count(),
                'late' => $userAttendances->where('status', 'late')->count(),
                'attendance_rate' => $userAttendances->count() > 0 
                    ? round(($userAttendances->where('status', 'on_time')->count() / $userAttendances->count()) * 100, 2)
                    : 0,
                'attendances' => $userAttendances,
            ];
        });

        // Get all teachers for filter
        $teachers = User::where('group', 'guru')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('kepala-sekolah.laporan.guru', compact(
            'teacherAttendances',
            'teachers',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Lesson attendance report
     */
    public function lessonAttendance(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'classroom_id' => 'nullable|exists:classrooms,id',
        ]);

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

        $query = Attendance::with(['schedule.subject', 'schedule.classroom', 'schedule.teacher', 'user'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($request->classroom_id) {
            $query->whereHas('schedule', function ($q) use ($request) {
                $q->where('classroom_id', $request->classroom_id);
            });
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('time_in', 'desc')
            ->get();

        // Group by classroom
        $classroomAttendances = $attendances->groupBy(function ($attendance) {
            return $attendance->schedule->classroom_id ?? 'unknown';
        })->map(function ($classAttendances, $classroomId) {
            $classroom = $classAttendances->first()->schedule->classroom ?? null;
            
            if (!$classroom) {
                return null;
            }

            // Group by subject
            $subjectStats = $classAttendances->groupBy(function ($attendance) {
                return $attendance->schedule->subject_id ?? 'unknown';
            })->map(function ($subjectAttendances) {
                $subject = $subjectAttendances->first()->schedule->subject ?? null;
                $teacher = $subjectAttendances->first()->schedule->teacher ?? null;
                
                return [
                    'subject' => $subject?->name,
                    'teacher' => $teacher?->name,
                    'total' => $subjectAttendances->count(),
                    'on_time' => $subjectAttendances->where('status', 'on_time')->count(),
                    'late' => $subjectAttendances->where('status', 'late')->count(),
                ];
            })->filter()->values();

            return [
                'classroom' => $classroom,
                'total_lessons' => $classAttendances->count(),
                'completed' => $classAttendances->whereNotNull('time_out')->count(),
                'on_time' => $classAttendances->where('status', 'on_time')->count(),
                'late' => $classAttendances->where('status', 'late')->count(),
                'subjects' => $subjectStats,
            ];
        })->filter()->values();

        // Get all classrooms for filter
        $classrooms = Classroom::orderBy('name')->get();

        return view('kepala-sekolah.laporan.pembelajaran', compact(
            'classroomAttendances',
            'classrooms',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export report
     */
    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:teacher,lesson',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|in:pdf,excel',
        ]);

        // TODO: Implement PDF/Excel export
        // This would use libraries like Laravel Excel or DomPDF

        return back()->with('info', 'Export feature akan segera diimplementasikan');
    }
}
