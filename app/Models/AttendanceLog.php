<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class AttendanceLog extends Model
{
    use HasUuid;

    protected $table = 'attendance_logs';

    protected $fillable = [
        'employee_id',
        'device_user_id',
        'punched_at',
        'biometric_device_id',
        'status',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'punched_at' => 'datetime',
    ];
}
