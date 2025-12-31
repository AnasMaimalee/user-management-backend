<?php

namespace App\Http\Controllers\Api\Attendance;
use App\Events\AttendanceRecorded;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\DailyAttendance;
use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    // AdminAttendanceController.php
    public function today()
    {
        $latestDate = \App\Models\DailyAttendance::max('attendance_date');

        return response()->json(
            \App\Models\DailyAttendance::with('employee')
                ->whereDate('attendance_date', $latestDate)
                ->get()
        );
    }


    public function report(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date',
        ]);

        return response()->json(
            DailyAttendance::with('employee')
                ->whereBetween('attendance_date', [
                    $request->from,
                    $request->to
                ])
                ->get()
        );
    }

    public function employee(Employee $employee)
    {
        return response()->json(
            $employee->dailyAttendances()
                ->latest('attendance_date')
                ->get()
        );
    }


    public function record(Request $request)
    {
        $attendance = DailyAttendance::create([
            'employee_id' => $request->employee_id,
            'status' => $request->status,
            'worked_minutes' => $request->worked_minutes,
            'late_minutes' => $request->late_minutes,
            'attendance_date' => now(),
        ]);

        // Broadcast event
        event(new AttendanceRecorded($attendance));

        return response()->json($attendance);
    }
}
