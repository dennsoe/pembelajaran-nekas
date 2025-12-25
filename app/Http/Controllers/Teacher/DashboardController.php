<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
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
            ->orderBy('time_in')
            ->get();

        // This month's statistics
        $thisMonthStart = $today->copy()->startOfMonth();
        $thisMonthEnd = $today->copy()->endOfMonth();

        $thisMonthStats = [
            'total_schedules' => Schedule::where('teacher_id', $user->id)
                ->where('is_active', true)
                ->count(),
            'total_attendances' => Attendance::where('user_id', $user->id)
                ->whereBetween('date', [$thisMonthStart, $thisMonthEnd])
                ->count(),
            'on_time' => Attendance::where('user_id', $user->id)
                ->whereBetween('date', [$thisMonthStart, $thisMonthEnd])
                ->where('status', 'on_time')
                ->count(),
            'late' => Attendance::where('user_id', $user->id)
                ->whereBetween('date', [$thisMonthStart, $thisMonthEnd])
                ->where('status', 'late')
                ->count(),
        ];

        // Pending leave requests
        $pendingLeaveRequests = LeaveRequest::where('teacher_id', $user->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent attendances
        $recentAttendances = Attendance::with(['schedule.subject', 'schedule.classroom'])
            ->where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->orderBy('time_in', 'desc')
            ->limit(10)
            ->get();

        return view('guru.dashboard', compact(
            'todaySchedules',
            'todayAttendances',
            'thisMonthStats',
            'pendingLeaveRequests',
            'recentAttendances'
        ));
    }
}
