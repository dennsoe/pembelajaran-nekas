<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Log activity
     */
    public function log(
        User $user,
        string $action,
        string $modelType,
        string $modelId,
        ?array $oldValues = null,
        ?array $newValues = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log login
     */
    public function logLogin(User $user): ActivityLog
    {
        return $this->log($user, 'login', User::class, $user->id);
    }

    /**
     * Log logout
     */
    public function logLogout(User $user): ActivityLog
    {
        return $this->log($user, 'logout', User::class, $user->id);
    }

    /**
     * Log CRUD operations
     */
    public function logCreate($model, User $user): ActivityLog
    {
        return $this->log(
            $user,
            'create',
            get_class($model),
            $model->id,
            null,
            $model->toArray()
        );
    }

    public function logUpdate($model, User $user, array $oldValues): ActivityLog
    {
        return $this->log(
            $user,
            'update',
            get_class($model),
            $model->id,
            $oldValues,
            $model->toArray()
        );
    }

    public function logDelete($model, User $user): ActivityLog
    {
        return $this->log(
            $user,
            'delete',
            get_class($model),
            $model->id,
            $model->toArray(),
            null
        );
    }

    /**
     * Log attendance operations
     */
    public function logAttendanceIn($attendance, User $user): ActivityLog
    {
        return $this->log(
            $user,
            'attendance_in',
            get_class($attendance),
            $attendance->id,
            null,
            [
                'schedule_id' => $attendance->schedule_id,
                'time_in' => $attendance->time_in,
                'method' => $attendance->method_in,
            ]
        );
    }

    public function logAttendanceOut($attendance, User $user): ActivityLog
    {
        return $this->log(
            $user,
            'attendance_out',
            get_class($attendance),
            $attendance->id,
            null,
            [
                'schedule_id' => $attendance->schedule_id,
                'time_out' => $attendance->time_out,
                'method' => $attendance->method_out,
            ]
        );
    }

    /**
     * Get activity logs dengan filter
     */
    public function getActivityLogs(array $filters = [], int $perPage = 50)
    {
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (!empty($filters['model_type'])) {
            $query->where('model_type', $filters['model_type']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage);
    }
}
