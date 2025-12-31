<?php

// app/Services/Attendance/AttendanceProcessor.php

namespace App\Services\Attendance;

use App\Models\{
    AttendanceLog,
    DailyAttendance,
    Employee
};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceProcessor
{
    public function processEmployeeForDate(Employee $employee, Carbon $date): void
    {
        DB::transaction(function () use ($employee, $date) {

            // Already processed?
            if (
                DailyAttendance::where('employee_id', $employee->id)
                    ->whereDate('attendance_date', $date)
                    ->exists()
            ) {
                return;
            }

            // Fetch logs
            $logs = AttendanceLog::where('employee_id', $employee->id)
                ->whereDate('punched_at', $date)
                ->orderBy('punched_at')
                ->get();

            $calculator = new AttendanceCalculator();
            $result = $calculator->calculate($logs, $date);

            DailyAttendance::create([
                'employee_id' => $employee->id,
                'attendance_date' => $date,
                'clock_in' => $result['clock_in'] ?? null,
                'clock_out' => $result['clock_out'] ?? null,
                'worked_minutes' => $result['worked_minutes'],
                'status' => $result['status'],
            ]);
        });
    }
}
