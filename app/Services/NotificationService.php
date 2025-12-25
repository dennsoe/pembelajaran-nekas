<?php

namespace App\Services;

use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Notifikasi guru belum absen
     */
    public function notifyTeacherNotAttended(Attendance $attendance): void
    {
        $teacher = $attendance->user;
        $schedule = $attendance->schedule;
        
        // Notify kurikulum
        $kurikulum = User::where('group', 'kurikulum')->get();
        
        // TODO: Implement notification logic
        // Could use Laravel Notification, Database Notification, or Email
        
        foreach ($kurikulum as $user) {
            // Example: $user->notify(new TeacherNotAttendedNotification($teacher, $schedule));
        }
    }

    /**
     * Notifikasi pengajuan izin baru
     */
    public function notifyNewLeaveRequest(LeaveRequest $leaveRequest): void
    {
        $teacher = $leaveRequest->teacher;
        
        // Notify kurikulum
        $kurikulum = User::where('group', 'kurikulum')->get();
        
        foreach ($kurikulum as $user) {
            // Example: $user->notify(new NewLeaveRequestNotification($teacher, $leaveRequest));
        }
    }

    /**
     * Notifikasi izin disetujui/ditolak
     */
    public function notifyLeaveRequestDecision(LeaveRequest $leaveRequest): void
    {
        $teacher = $leaveRequest->teacher;
        
        // Example: $teacher->notify(new LeaveRequestDecisionNotification($leaveRequest));
    }

    /**
     * Notifikasi guru pengganti di-assign
     */
    public function notifySubstituteTeacherAssigned(User $substituteTeacher, $substitution): void
    {
        // Example: $substituteTeacher->notify(new SubstituteAssignmentNotification($substitution));
    }

    /**
     * Notifikasi ke kepala sekolah tentang ketidakhadiran
     */
    public function notifyPrincipalAbsent(Attendance $attendance): void
    {
        $kepalaSekolah = User::where('group', 'kepala_sekolah')->first();
        
        if ($kepalaSekolah) {
            // Example: $kepalaSekolah->notify(new TeacherAbsentNotification($attendance));
        }
    }

    /**
     * Send email notification
     */
    public function sendEmail(User $user, string $subject, string $message): void
    {
        // TODO: Implement email sending
        // Could use Laravel Mail
    }

    /**
     * Create database notification
     */
    public function createDatabaseNotification(User $user, array $data): void
    {
        // Using Laravel's database notifications
        $user->notify(new \Illuminate\Notifications\DatabaseNotification($data));
    }

    /**
     * Get unread notifications untuk user
     */
    public function getUnreadNotifications(User $user, int $limit = 10)
    {
        return $user->unreadNotifications()->take($limit)->get();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(string $notificationId): void
    {
        $notification = \Illuminate\Notifications\DatabaseNotification::find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }
}
