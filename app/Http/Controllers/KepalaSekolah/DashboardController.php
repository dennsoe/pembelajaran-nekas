<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::now();
        $thisMonth = $today->copy()->startOfMonth();
        $thisMonthEnd = $today->copy()->endOfMonth();

        // Teacher statistics
        $teacherStats = [
            'total' => User::where('group', 'guru')->where('is_active', true)->count(),
            'present_today' => Attendance::whereDate('date', $today)
                ->distinct('user_id')
                ->count('user_id'),
            'on_leave_today' => LeaveRequest::where('status', 'approved')
                ->whereDate('leave_date', $today)
                ->distinct('teacher_id')
                ->count('teacher_id'),
        ];

        // Attendance statistics for this month
        $attendanceStats = [
            'total' => Attendance::whereBetween('date', [$thisMonth, $thisMonthEnd])->count(),
            'on_time' => Attendance::whereBetween('date', [$thisMonth, $thisMonthEnd])
                ->where('status', 'on_time')
                ->count(),
            'late' => Attendance::whereBetween('date', [$thisMonth, $thisMonthEnd])
                ->where('status', 'late')
                ->count(),
            'pending_validation' => Attendance::where('is_validated', false)->count(),
        ];

        // Leave requests statistics
        $leaveStats = [
            'pending' => LeaveRequest::where('status', 'pending')->count(),
            'approved_today' => LeaveRequest::where('status', 'approved')
                ->whereDate('leave_date', $today)
                ->count(),
            'this_month' => LeaveRequest::whereBetween('leave_date', [$thisMonth, $thisMonthEnd])->count(),
        ];

        // Today's schedules summary
        $todaySchedules = Schedule::where('day_of_week', $today->dayOfWeekIso)
            ->where('is_active', true)
            ->count();

        // Recent attendances
        $recentAttendances = Attendance::with(['user', 'schedule.subject', 'schedule.classroom'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Attendance trend (last 7 days)
        $attendanceTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $attendanceTrend[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'count' => Attendance::whereDate('date', $date)->count(),
                'on_time' => Attendance::whereDate('date', $date)->where('status', 'on_time')->count(),
                'late' => Attendance::whereDate('date', $date)->where('status', 'late')->count(),
            ];
        }

        return view('kepala-sekolah.dashboard', compact(
            'teacherStats',
            'attendanceStats',
            'leaveStats',
            'todaySchedules',
            'recentAttendances',
            'attendanceTrend'
        ));
    }
}
