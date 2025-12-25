<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /**
     * Get all schedules for authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $schedules = Schedule::with(['subject', 'classroom', 'lessonPeriod', 'academicYear'])
            ->where('teacher_id', $user->id)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('lesson_period_id')
            ->get();

        return response()->json([
            'message' => 'Schedules retrieved successfully',
            'data' => $schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'day' => $schedule->day_name,
                    'day_of_week' => $schedule->day_of_week,
                    'subject' => $schedule->subject->name,
                    'classroom' => $schedule->classroom->name,
                    'lesson_period' => [
                        'start_time' => $schedule->lessonPeriod->start_time,
                        'end_time' => $schedule->lessonPeriod->end_time,
                        'period_number' => $schedule->lessonPeriod->period_number,
                    ],
                    'academic_year' => $schedule->academicYear->name,
                    'room_number' => $schedule->room_number,
                ];
            }),
        ]);
    }

    /**
     * Get today's schedules
     */
    public function today(Request $request)
    {
        $user = $request->user();
        $today = Carbon::now()->dayOfWeekIso; // 1 = Monday, 7 = Sunday

        $schedules = Schedule::with(['subject', 'classroom', 'lessonPeriod', 'academicYear'])
            ->where('teacher_id', $user->id)
            ->where('day_of_week', $today)
            ->where('is_active', true)
            ->orderBy('lesson_period_id')
            ->get();

        return response()->json([
            'message' => 'Today\'s schedules retrieved successfully',
            'date' => Carbon::now()->format('Y-m-d'),
            'day' => Carbon::now()->format('l'),
            'data' => $schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'subject' => $schedule->subject->name,
                    'classroom' => $schedule->classroom->name,
                    'lesson_period' => [
                        'start_time' => $schedule->lessonPeriod->start_time,
                        'end_time' => $schedule->lessonPeriod->end_time,
                        'period_number' => $schedule->lessonPeriod->period_number,
                    ],
                    'room_number' => $schedule->room_number,
                ];
            }),
        ]);
    }

    /**
     * Get schedule detail
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $schedule = Schedule::with(['subject', 'classroom', 'lessonPeriod', 'academicYear', 'teacher'])
            ->where('id', $id)
            ->where('teacher_id', $user->id)
            ->first();

        if (!$schedule) {
            return response()->json([
                'message' => 'Schedule not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Schedule retrieved successfully',
            'data' => [
                'id' => $schedule->id,
                'day' => $schedule->day_name,
                'day_of_week' => $schedule->day_of_week,
                'subject' => [
                    'id' => $schedule->subject->id,
                    'name' => $schedule->subject->name,
                    'code' => $schedule->subject->code,
                ],
                'classroom' => [
                    'id' => $schedule->classroom->id,
                    'name' => $schedule->classroom->name,
                    'level' => $schedule->classroom->level,
                    'major' => $schedule->classroom->major,
                ],
                'lesson_period' => [
                    'start_time' => $schedule->lessonPeriod->start_time,
                    'end_time' => $schedule->lessonPeriod->end_time,
                    'period_number' => $schedule->lessonPeriod->period_number,
                    'duration_minutes' => $schedule->lessonPeriod->duration_minutes,
                ],
                'academic_year' => $schedule->academicYear->name,
                'room_number' => $schedule->room_number,
                'is_active' => $schedule->is_active,
            ],
        ]);
    }
}
