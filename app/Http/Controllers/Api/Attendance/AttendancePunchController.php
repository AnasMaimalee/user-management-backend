<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\DailyAttendance;
use App\Events\AttendanceRecorded;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendancePunchController extends Controller
{
    public function punch(Request $request)
    {
        $request->validate([
            'device_user_id' => 'required|integer',
        ]);

        $uid = $request->device_user_id;

        $employee = Employee::where('device_user_id', $uid)->first();

        if (!$employee) {
            return response()->json([
                'message' => 'Unknown fingerprint. Employee not enrolled.'
            ], 404);
        }

        // Complete enrollment on first successful scan
        if (!$employee->fingerprint_enrolled_at) {
            $employee->update(['fingerprint_enrolled_at' => now()]);
        }

        $today = Carbon::today();

        // Find or create today's attendance record
        $attendance = DailyAttendance::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'attendance_date' => $today,
            ],
            [
                'clock_in' => now(),
                'status' => 'present',
                'worked_minutes' => 0,
                'late_minutes' => 0,
            ]
        );

        // If already clocked in → this is clock out
        if (!$attendance->wasRecentlyCreated && $attendance->clock_in && !$attendance->clock_out) {
            $workedMinutes = Carbon::parse($attendance->clock_in)->diffInMinutes(now());

            $lateMinutes = 0;
            $expectedIn = $today->copy()->setTime(9, 0); // 9:00 AM
            if ($attendance->clock_in->gt($expectedIn)) {
                $lateMinutes = Carbon::parse($attendance->clock_in)->diffInMinutes($expectedIn);
            }

            $attendance->update([
                'clock_out' => now(),
                'worked_minutes' => $workedMinutes,
                'late_minutes' => $lateMinutes,
                'status' => $lateMinutes > 0 ? 'late' : 'present',
            ]);
        }

        // Broadcast realtime update to admin dashboard
        event(new AttendanceRecorded($attendance->load('employee')));

        return response()->json([
            'message' => 'Attendance recorded successfully',
            'action' => $attendance->clock_out ? 'Clock Out' : 'Clock In',
            'employee' => $employee->only(['id', 'first_name', 'last_name', 'employee_code']),
            'attendance' => $attendance,
        ]);
    }
}
