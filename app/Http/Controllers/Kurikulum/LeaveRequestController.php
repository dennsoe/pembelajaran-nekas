<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaveRequestController extends Controller
{
    protected $activityLogService;
    protected $notificationService;

    public function __construct(
        ActivityLogService $activityLogService,
        NotificationService $notificationService
    ) {
        $this->activityLogService = $activityLogService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display leave requests
     */
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['teacher', 'schedules.subject', 'schedules.classroom', 'approver']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        } else {
            // Default: show pending only
            $query->where('status', 'pending');
        }

        // Filter by date
        if ($request->has('leave_date') && $request->leave_date) {
            $query->whereDate('leave_date', $request->leave_date);
        }

        // Filter by teacher
        if ($request->has('teacher_id') && $request->teacher_id) {
            $query->where('teacher_id', $request->teacher_id);
        }

        $leaveRequests = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get teachers for filter
        $teachers = \App\Models\User::where('group', 'guru')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('kurikulum.izin.index', compact('leaveRequests', 'teachers'));
    }

    /**
     * Approve leave request
     */
    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Permohonan izin sudah diproses');
        }

        $user = Auth::user();

        try {
            DB::beginTransaction();

            $leaveRequest->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'approval_notes' => $request->notes,
            ]);

            // Log activity
            $this->activityLogService->log(
                $user->id,
                'leave_request',
                'approve',
                'LeaveRequest',
                $leaveRequest->id,
                "Approved leave request from {$leaveRequest->teacher->name} for {$leaveRequest->leave_date}"
            );

            // Send notification to teacher
            $this->notificationService->send(
                $leaveRequest->teacher_id,
                'leave_request_approved',
                "Your leave request for {$leaveRequest->leave_date} has been approved"
            );

            DB::commit();

            return back()->with('success', 'Permohonan izin disetujui');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyetujui: ' . $e->getMessage());
        }
    }

    /**
     * Reject leave request
     */
    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'notes' => 'required|string|max:500',
        ]);

        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Permohonan izin sudah diproses');
        }

        $user = Auth::user();

        try {
            DB::beginTransaction();

            $leaveRequest->update([
                'status' => 'rejected',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'approval_notes' => $request->notes,
            ]);

            // Log activity
            $this->activityLogService->log(
                $user->id,
                'leave_request',
                'reject',
                'LeaveRequest',
                $leaveRequest->id,
                "Rejected leave request from {$leaveRequest->teacher->name} for {$leaveRequest->leave_date}: {$request->notes}"
            );

            // Send notification to teacher
            $this->notificationService->send(
                $leaveRequest->teacher_id,
                'leave_request_rejected',
                "Your leave request for {$leaveRequest->leave_date} has been rejected: {$request->notes}"
            );

            DB::commit();

            return back()->with('success', 'Permohonan izin ditolak');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menolak: ' . $e->getMessage());
        }
    }
}
