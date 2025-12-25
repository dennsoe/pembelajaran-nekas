<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    use HasFactory, HasUlids;

    const CREATED_AT = null;

    protected $fillable = [
        'method',
        'tolerance_before',
        'tolerance_after',
        'qr_expiry_minutes',
        'school_latitude',
        'school_longitude',
        'location_radius',
        'face_match_threshold',
    ];

    protected $casts = [
        'tolerance_before' => 'integer',
        'tolerance_after' => 'integer',
        'qr_expiry_minutes' => 'integer',
        'school_latitude' => 'decimal:7',
        'school_longitude' => 'decimal:7',
        'location_radius' => 'integer',
        'face_match_threshold' => 'decimal:2',
        'method' => 'string',
    ];
}
