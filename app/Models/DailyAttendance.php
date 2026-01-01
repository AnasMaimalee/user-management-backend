<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class DailyAttendance extends Model
{
    use HasUuid;

    protected $table = 'daily_attendances';

    protected $fillable = [
        'employee_id',
        'attendance_date',
        'clock_in',
        'clock_out',
        'worked_minutes',
        'status',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'attendance_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }



}
