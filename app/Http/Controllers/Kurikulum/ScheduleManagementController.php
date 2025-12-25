<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Classroom;
use App\Models\User;
use App\Models\LessonPeriod;
use App\Models\AcademicYear;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScheduleManagementController extends Controller
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Display schedule list
     */
    public function index(Request $request)
    {
        $query = Schedule::with([
            'teacher',
            'subject',
            'classroom',
            'lessonPeriod',
            'academicYear'
        ]);

        // Filter by day
        if ($request->has('day') && $request->day) {
            $query->where('day_of_week', $request->day);
        }

        // Filter by classroom
        if ($request->has('classroom_id') && $request->classroom_id) {
            $query->where('classroom_id', $request->classroom_id);
        }

        // Filter by teacher
        if ($request->has('teacher_id') && $request->teacher_id) {
            $query->where('teacher_id', $request->teacher_id);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $schedules = $query->orderBy('day_of_week')
            ->orderBy('lesson_period_id')
            ->paginate(20);

        // Get filter options
        $classrooms = Classroom::orderBy('name')->get();
        $teachers = User::where('group', 'guru')->where('is_active', true)->orderBy('name')->get();

        return view('kurikulum.jadwal.index', compact('schedules', 'classrooms', 'teachers'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->get();
        $teachers = User::where('group', 'guru')->where('is_active', true)->orderBy('name')->get();
        $lessonPeriods = LessonPeriod::orderBy('day_of_week')->orderBy('period_number')->get();
        $academicYears = AcademicYear::where('is_active', true)->orderBy('start_date', 'desc')->get();

        return view('kurikulum.jadwal.create', compact(
            'subjects',
            'classrooms',
            'teachers',
            'lessonPeriods',
            'academicYears'
        ));
    }

    /**
     * Store new schedule
     */
    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'day_of_week' => 'required|integer|between:1,7',
            'lesson_period_id' => 'required|exists:lesson_periods,id',
            'room_number' => 'nullable|string|max:50',
        ]);

        $user = Auth::user();

        try {
            DB::beginTransaction();

            // Check for schedule conflicts
            $conflict = Schedule::where('classroom_id', $request->classroom_id)
                ->where('day_of_week', $request->day_of_week)
                ->where('lesson_period_id', $request->lesson_period_id)
                ->where('academic_year_id', $request->academic_year_id)
                ->where('is_active', true)
                ->exists();

            if ($conflict) {
                return back()->with('error', 'Jadwal bentrok dengan jadwal lain')->withInput();
            }

            // Check teacher conflict
            $teacherConflict = Schedule::where('teacher_id', $request->teacher_id)
                ->where('day_of_week', $request->day_of_week)
                ->where('lesson_period_id', $request->lesson_period_id)
                ->where('academic_year_id', $request->academic_year_id)
                ->where('is_active', true)
                ->exists();

            if ($teacherConflict) {
                return back()->with('error', 'Guru sudah memiliki jadwal di waktu yang sama')->withInput();
            }

            $schedule = Schedule::create($request->all());

            // Log activity
            $this->activityLogService->log(
                $user->id,
                'schedule_management',
                'create',
                'Schedule',
                $schedule->id,
                "Created schedule for {$schedule->teacher->name} - {$schedule->subject->name} at {$schedule->classroom->name}"
            );

            DB::commit();

            return redirect()->route('kurikulum.jadwal')->with('success', 'Jadwal berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menambahkan jadwal: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show edit form
     */
    public function edit(Schedule $schedule)
    {
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->get();
        $teachers = User::where('group', 'guru')->where('is_active', true)->orderBy('name')->get();
        $lessonPeriods = LessonPeriod::orderBy('day_of_week')->orderBy('period_number')->get();
        $academicYears = AcademicYear::where('is_active', true)->orderBy('start_date', 'desc')->get();

        return view('kurikulum.jadwal.edit', compact(
            'schedule',
            'subjects',
            'classrooms',
            'teachers',
            'lessonPeriods',
            'academicYears'
        ));
    }

    /**
     * Update schedule
     */
    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'day_of_week' => 'required|integer|between:1,7',
            'lesson_period_id' => 'required|exists:lesson_periods,id',
            'room_number' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $user = Auth::user();

        try {
            DB::beginTransaction();

            // Check for schedule conflicts (excluding current schedule)
            $conflict = Schedule::where('id', '!=', $schedule->id)
                ->where('classroom_id', $request->classroom_id)
                ->where('day_of_week', $request->day_of_week)
                ->where('lesson_period_id', $request->lesson_period_id)
                ->where('academic_year_id', $request->academic_year_id)
                ->where('is_active', true)
                ->exists();

            if ($conflict) {
                return back()->with('error', 'Jadwal bentrok dengan jadwal lain')->withInput();
            }

            // Check teacher conflict (excluding current schedule)
            $teacherConflict = Schedule::where('id', '!=', $schedule->id)
                ->where('teacher_id', $request->teacher_id)
                ->where('day_of_week', $request->day_of_week)
                ->where('lesson_period_id', $request->lesson_period_id)
                ->where('academic_year_id', $request->academic_year_id)
                ->where('is_active', true)
                ->exists();

            if ($teacherConflict) {
                return back()->with('error', 'Guru sudah memiliki jadwal di waktu yang sama')->withInput();
            }

            $schedule->update($request->all());

            // Log activity
            $this->activityLogService->log(
                $user->id,
                'schedule_management',
                'update',
                'Schedule',
                $schedule->id,
                "Updated schedule for {$schedule->teacher->name} - {$schedule->subject->name}"
            );

            DB::commit();

            return redirect()->route('kurikulum.jadwal')->with('success', 'Jadwal berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui jadwal: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Delete schedule
     */
    public function destroy(Schedule $schedule)
    {
        $user = Auth::user();

        try {
            DB::beginTransaction();

            // Soft delete by setting is_active to false
            $schedule->update(['is_active' => false]);

            // Log activity
            $this->activityLogService->log(
                $user->id,
                'schedule_management',
                'delete',
                'Schedule',
                $schedule->id,
                "Deactivated schedule for {$schedule->teacher->name} - {$schedule->subject->name}"
            );

            DB::commit();

            return back()->with('success', 'Jadwal berhasil dinonaktifkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menonaktifkan jadwal: ' . $e->getMessage());
        }
    }
}
