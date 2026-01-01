<?php

namespace App\Services\Attendance;

use App\Models\Employee;
use App\Models\DailyAttendance;
use Carbon\Carbon;

class AttendanceStatsService
{
    public function getTodayStats(): array
    {
        $today = Carbon::today();

        $totalEmployees = Employee::count();

        $presentToday = DailyAttendance::whereDate('attendance_date', $today)
            ->where('status', 'present')
            ->count();

        $absentToday = $totalEmployees - $presentToday;

        return [
            'total_employees' => $totalEmployees,
            'present_today'   => $presentToday,
            'absent_today'    => $absentToday,
        ];
    }
}
