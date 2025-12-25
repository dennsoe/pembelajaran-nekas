<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $student = Auth::guard('student')->user();
        
        if (!$student->classroom_id) {
            return view('siswa.dashboard')->with('error', 'Anda belum terdaftar di kelas manapun');
        }

        $today = Carbon::now();
        $dayOfWeek = $today->dayOfWeekIso;

        // Get today's schedules
        $todaySchedules = Schedule::with(['subject', 'teacher', 'lessonPeriod'])
            ->where('classroom_id', $student->classroom_id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('lesson_period_id')
            ->get();

        // Get this week's schedules
        $weekSchedules = Schedule::with(['subject', 'teacher', 'lessonPeriod'])
            ->where('classroom_id', $student->classroom_id)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('lesson_period_id')
            ->get()
            ->groupBy('day_of_week');

        return view('siswa.dashboard', compact('todaySchedules', 'weekSchedules', 'student'));
    }
}
