<?php

namespace App\Services\Attendance;

use App\Models\AttendanceSetting;
use Carbon\Carbon;

class AttendanceCalculator
{
    public function calculate($logs, $date): array
    {
        // No logs → absent
        if ($logs->isEmpty()) {
            return [
                'status' => 'absent',
                'worked_minutes' => 0,
                'late_minutes' => 0,
                'early_minutes' => 0,
            ];
        }

        $setting = AttendanceSetting::first();

        // First punch = check in, last punch = check out
        $checkIn  = Carbon::parse($logs->first()->punched_at);
        $checkOut = Carbon::parse($logs->last()->punched_at);

        $workedMinutes = $checkOut->diffInMinutes($checkIn);

        // Work start time
        $workStart = Carbon::parse($date . ' ' . $setting->work_start_time);

        $lateMinutes = max(0, $checkIn->diffInMinutes($workStart, false));
        $lateMinutes = $lateMinutes > 0 ? $lateMinutes : 0;

        // Status logic
        if ($workedMinutes >= $setting->full_day_minutes) {
            $status = 'present';
        } elseif ($workedMinutes >= $setting->half_day_minutes) {
            $status = 'half_day';
        } else {
            $status = 'absent';
        }

        return [
            'status' => $status,
            'worked_minutes' => $workedMinutes,
            'late_minutes' => $lateMinutes,
            'early_minutes' => 0,
        ];
    }
}
