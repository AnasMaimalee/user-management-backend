<?php

namespace App\Services\Attendance;

use App\Models\Employee;
use App\Models\DailyAttendance;
use Carbon\Carbon;

class AttendanceSummaryService
{
    public function monthlySummary(Employee $employee): array
    {
        $start = now()->startOfMonth();
        $end   = now()->endOfMonth();

        $records = DailyAttendance::where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$start, $end])
            ->get();

        $present = $records->where('status', 'present')->count();
        $half    = $records->where('status', 'half_day')->count();
        $absent  = $records->where('status', 'absent')->count();

        $lateMinutes = $records->sum('late_minutes');

        $totalDays = max(1, $records->count());

        $percentage = round(
            (($present + ($half * 0.5)) / $totalDays) * 100,
            1
        );

        $performanceScore = max(
            0,
            100 - ($absent * 10) - ($lateMinutes / 10)
        );

        return [
            'present_days' => $present,
            'half_days' => $half,
            'absent_days' => $absent,
            'late_minutes' => $lateMinutes,
            'attendance_percentage' => $percentage,
            'performance_score' => round($performanceScore),
        ];
    }
}

