<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LeaveRequest extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'teacher_id',
        'schedule_id',
        'date',
        'type',
        'reason',
        'attachment',
        'status',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
        'type' => 'string',
        'status' => 'string',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function substituteTeacher(): HasOne
    {
        return $this->hasOne(SubstituteTeacher::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
