<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicCalendar extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'academic_year_id',
        'date',
        'title',
        'type',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'type' => 'string',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
