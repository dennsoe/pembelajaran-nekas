<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasUlids;
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nip',
        'name',
        'email',
        'password',
        'raw_password',
        'group',
        'phone',
        'gender',
        'birth_date',
        'birth_place',
        'address',
        'city',
        'education_id',
        'division_id',
        'job_title_id',
        'profile_photo_path',
        'face_encoding',
        'face_photo_path',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'raw_password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'datetime:Y-m-d',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public static $groups = ['guru', 'kurikulum', 'kepala_sekolah', 'admin', 'superadmin'];

    final public function getIsGuruAttribute(): bool
    {
        return $this->group === 'guru';
    }

    final public function getIsKurikulumAttribute(): bool
    {
        return $this->group === 'kurikulum';
    }

    final public function getIsKepalaSekolahAttribute(): bool
    {
        return $this->group === 'kepala_sekolah';
    }

    final public function getIsAdminAttribute(): bool
    {
        return $this->group === 'admin' || $this->isSuperadmin;
    }

    final public function getIsSuperadminAttribute(): bool
    {
        return $this->group === 'superadmin';
    }

    final public function getIsNotAdminAttribute(): bool
    {
        return !$this->isAdmin;
    }

    public function education()
    {
        return $this->belongsTo(Education::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function jobTitle()
    {
        return $this->belongsTo(JobTitle::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'teacher_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'teacher_id');
    }

    public function approvedLeaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by');
    }

    public function originalSubstitutions()
    {
        return $this->hasMany(SubstituteTeacher::class, 'original_teacher_id');
    }

    public function substituteAssignments()
    {
        return $this->hasMany(SubstituteTeacher::class, 'substitute_teacher_id');
    }

    public function validatedAttendances()
    {
        return $this->hasMany(Attendance::class, 'validated_by');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
