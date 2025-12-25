<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\SubstituteTeacher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    /**
     * Display schedule list
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $schedules = Schedule::with(['subject', 'classroom', 'lessonPeriod', 'academicYear'])
            ->where('teacher_id', $user->id)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('lesson_period_id')
            ->get();

        // Group by day
        $groupedSchedules = $schedules->groupBy('day_of_week')->map(function ($daySchedules, $dayOfWeek) {
            return [
                'day' => Carbon::now()->startOfWeek()->addDays($dayOfWeek - 1)->format('l'),
                'day_of_week' => $dayOfWeek,
                'schedules' => $daySchedules,
            ];
        });

        return view('guru.jadwal.index', compact('groupedSchedules'));
    }

    /**
     * Show schedule detail
     */
    public function show(Schedule $schedule)
    {
        $user = Auth::user();

        if ($schedule->teacher_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $schedule->load(['subject', 'classroom', 'lessonPeriod', 'academicYear', 'classroom.students']);

        // Get attendance history for this schedule
        $attendances = $schedule->attendances()
            ->with('user')
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        return view('guru.jadwal.show', compact('schedule', 'attendances'));
    }

    /**
     * Show substitute teacher history
     */
    public function substitute(Request $request)
    {
        $user = Auth::user();

        // Where user is the original teacher
        $asOriginalTeacher = SubstituteTeacher::with([
            'schedule.subject',
            'schedule.classroom',
            'substituteTeacher'
        ])
            ->whereHas('schedule', function ($query) use ($user) {
                $query->where('teacher_id', $user->id);
            })
            ->orderBy('date', 'desc')
            ->get();

        // Where user is the substitute teacher
        $asSubstituteTeacher = SubstituteTeacher::with([
            'schedule.subject',
            'schedule.classroom',
            'schedule.teacher'
        ])
            ->where('substitute_teacher_id', $user->id)
            ->orderBy('date', 'desc')
            ->get();

        return view('guru.pengganti.index', compact('asOriginalTeacher', 'asSubstituteTeacher'));
    }
}
