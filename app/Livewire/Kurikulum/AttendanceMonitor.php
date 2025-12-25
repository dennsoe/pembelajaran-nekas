<?php

namespace App\Livewire\Kurikulum;

use App\Models\Attendance;
use App\Models\Schedule;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceMonitor extends Component
{
    use WithPagination;

    public $selectedDate;
    public $filterStatus = 'all';
    public $filterValidation = 'pending';
    public $refreshInterval = 30; // 30 seconds

    protected $queryString = ['selectedDate', 'filterStatus', 'filterValidation'];

    public function mount()
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
    }

    public function updatedSelectedDate()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function updatedFilterValidation()
    {
        $this->resetPage();
    }

    public function refreshData()
    {
        $this->dispatch('dataRefreshed');
    }

    public function render()
    {
        $date = Carbon::parse($this->selectedDate);
        $dayOfWeek = $date->dayOfWeekIso;

        // Get today's schedules
        $todaySchedules = Schedule::with(['teacher', 'subject', 'classroom', 'lessonPeriod'])
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('lesson_period_id')
            ->get();

        // Get attendances
        $query = Attendance::with(['user', 'schedule.subject', 'schedule.classroom', 'schedule.lessonPeriod'])
            ->whereDate('date', $date);

        // Filter by status
        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        // Filter by validation
        if ($this->filterValidation === 'pending') {
            $query->where('is_validated', false);
        } elseif ($this->filterValidation === 'validated') {
            $query->where('is_validated', true);
        }

        $attendances = $query->orderBy('time_in', 'desc')->paginate(20);

        // Statistics
        $stats = [
            'total_schedules' => $todaySchedules->count(),
            'total_attendances' => Attendance::whereDate('date', $date)->count(),
            'checked_in' => Attendance::whereDate('date', $date)->whereNotNull('time_in')->count(),
            'checked_out' => Attendance::whereDate('date', $date)->whereNotNull('time_out')->count(),
            'on_time' => Attendance::whereDate('date', $date)->where('status', 'on_time')->count(),
            'late' => Attendance::whereDate('date', $date)->where('status', 'late')->count(),
            'pending_validation' => Attendance::whereDate('date', $date)->where('is_validated', false)->count(),
        ];

        return view('livewire.kurikulum.attendance-monitor', [
            'todaySchedules' => $todaySchedules,
            'attendances' => $attendances,
            'stats' => $stats,
        ]);
    }
}
