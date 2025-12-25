<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonPeriod extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'period_number',
        'day_of_week',
        'start_time',
        'end_time',
        'duration_minutes',
        'academic_year_id',
        'is_active',
    ];

    protected $casts = [
        'period_number' => 'integer',
        'day_of_week' => 'integer',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'duration_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
