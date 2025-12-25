<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\QrCode;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Get student profile
     */
    public function profile(Request $request)
    {
        $student = $request->user();
        $student->load('classroom');

        return response()->json([
            'message' => 'Profile retrieved successfully',
            'data' => [
                'id' => $student->id,
                'nis' => $student->nis,
                'nisn' => $student->nisn,
                'name' => $student->name,
                'email' => $student->email,
                'phone' => $student->phone,
                'gender' => $student->gender,
                'birth_date' => $student->birth_date,
                'birth_place' => $student->birth_place,
                'address' => $student->address,
                'classroom' => [
                    'id' => $student->classroom->id ?? null,
                    'name' => $student->classroom->name ?? null,
                    'level' => $student->classroom->level ?? null,
                    'major' => $student->classroom->major ?? null,
                ],
                'is_class_leader' => $student->is_class_leader,
                'is_active' => $student->is_active,
            ],
        ]);
    }

    /**
     * Get QR Code for class (only for class leaders)
     */
    public function qrCode(Request $request)
    {
        $student = $request->user();

        if (!$student->is_class_leader) {
            return response()->json([
                'message' => 'Only class leaders can access QR codes',
            ], 403);
        }

        if (!$student->classroom_id) {
            return response()->json([
                'message' => 'Student is not assigned to any classroom',
            ], 422);
        }

        // Get today's schedules for this classroom
        $today = Carbon::now()->dayOfWeekIso;
        $schedules = Schedule::where('classroom_id', $student->classroom_id)
            ->where('day_of_week', $today)
            ->where('is_active', true)
            ->with(['subject', 'lessonPeriod', 'teacher'])
            ->orderBy('lesson_period_id')
            ->get();

        // Get or create QR code for today
        $qrCode = QrCode::where('classroom_id', $student->classroom_id)
            ->whereDate('valid_from', '<=', Carbon::now())
            ->whereDate('valid_until', '>=', Carbon::now())
            ->where('is_active', true)
            ->first();

        if (!$qrCode) {
            // Create new QR code for today
            $qrCode = QrCode::create([
                'classroom_id' => $student->classroom_id,
                'code' => bin2hex(random_bytes(16)), // Generate random code
                'valid_from' => Carbon::now()->startOfDay(),
                'valid_until' => Carbon::now()->endOfDay(),
                'is_active' => true,
            ]);
        }

        return response()->json([
            'message' => 'QR code retrieved successfully',
            'data' => [
                'qr_code' => $qrCode->code,
                'valid_from' => $qrCode->valid_from,
                'valid_until' => $qrCode->valid_until,
                'classroom' => $student->classroom->name,
            ],
            'schedules' => $schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'subject' => $schedule->subject->name,
                    'teacher' => $schedule->teacher->name,
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
     * Get student's class schedule
     */
    public function schedule(Request $request)
    {
        $student = $request->user();

        if (!$student->classroom_id) {
            return response()->json([
                'message' => 'Student is not assigned to any classroom',
            ], 422);
        }

        $schedules = Schedule::where('classroom_id', $student->classroom_id)
            ->where('is_active', true)
            ->with(['subject', 'lessonPeriod', 'teacher', 'academicYear'])
            ->orderBy('day_of_week')
            ->orderBy('lesson_period_id')
            ->get();

        $groupedSchedules = $schedules->groupBy('day_of_week')->map(function ($daySchedules, $dayOfWeek) {
            return [
                'day' => Carbon::now()->startOfWeek()->addDays($dayOfWeek - 1)->format('l'),
                'day_of_week' => $dayOfWeek,
                'schedules' => $daySchedules->map(function ($schedule) {
                    return [
                        'id' => $schedule->id,
                        'subject' => $schedule->subject->name,
                        'teacher' => $schedule->teacher->name,
                        'lesson_period' => [
                            'start_time' => $schedule->lessonPeriod->start_time,
                            'end_time' => $schedule->lessonPeriod->end_time,
                            'period_number' => $schedule->lessonPeriod->period_number,
                        ],
                        'room_number' => $schedule->room_number,
                    ];
                }),
            ];
        })->values();

        return response()->json([
            'message' => 'Schedule retrieved successfully',
            'classroom' => $student->classroom->name,
            'data' => $groupedSchedules,
        ]);
    }

    /**
     * Get today's schedule
     */
    public function todaySchedule(Request $request)
    {
        $student = $request->user();

        if (!$student->classroom_id) {
            return response()->json([
                'message' => 'Student is not assigned to any classroom',
            ], 422);
        }

        $today = Carbon::now()->dayOfWeekIso;
        
        $schedules = Schedule::where('classroom_id', $student->classroom_id)
            ->where('day_of_week', $today)
            ->where('is_active', true)
            ->with(['subject', 'lessonPeriod', 'teacher'])
            ->orderBy('lesson_period_id')
            ->get();

        return response()->json([
            'message' => 'Today\'s schedule retrieved successfully',
            'date' => Carbon::now()->format('Y-m-d'),
            'day' => Carbon::now()->format('l'),
            'classroom' => $student->classroom->name,
            'data' => $schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'subject' => $schedule->subject->name,
                    'teacher' => $schedule->teacher->name,
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
}
