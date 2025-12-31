<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class AttendanceSetting extends Model
{
    use HasUuid;

    protected $table = 'attendance_settings';

    protected $fillable = [
        'work_start_time',
        'late_after',
        'work_end_time',
        'half_day_minutes',
        'full_day_minutes',
    ];

    public $incrementing = false;
    protected $keyType = 'string';
}
