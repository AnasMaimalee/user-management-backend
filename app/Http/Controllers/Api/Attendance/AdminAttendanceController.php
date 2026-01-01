<?php

namespace App\Http\Controllers\Api\Attendance;
use App\Events\AttendanceRecorded;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\DailyAttendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    // AdminAttendanceController.php

    public function today()
    {
        $today = Carbon::now()->toDateString(); // e.g., "2025-12-31"

        return response()->json(
            \App\Models\DailyAttendance::with('employee')
                ->whereDate('attendance_date', $today)
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
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'status' => 'required|string|in:present,late,absent,on_leave,holiday',
            'worked_minutes' => 'nullable|integer|min:0',
            'late_minutes' => 'nullable|integer|min:0',
        ]);

        $employee = Employee::findOrFail($request->employee_id);

        // Check if biometric device is connected/active
        $device = BiometricDevice::where('is_active', true)->first();

        if (!$device) {
            return response()->json([
                'message' => 'No biometric device detected. Please connect and activate a device.',
                'action_required' => 'admin_check_device'
            ], 503); // Service Unavailable
        }

        // Check if employee is enrolled
        if (!$employee->fingerprint_enrolled_at) {
            return response()->json([
                'message' => 'Biometric enrollment required. Please complete fingerprint enrollment before clocking in.',
                'action_required' => 'enroll_employee'
            ], 403);
        }

        $today = Carbon::today();

        // Prevent duplicate entry
        $existing = DailyAttendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Attendance already recorded for today.',
                'attendance' => $existing->load('employee'),
                'action_required' => 'already_recorded'
            ], 409);
        }

        $attendance = DailyAttendance::create([
            'employee_id' => $employee->id,
            'attendance_date' => $today,
            'clock_in' => now(),
            'status' => $request->status,
            'worked_minutes' => $request->worked_minutes ?? 0,
            'late_minutes' => $request->late_minutes ?? 0,
        ]);

        event(new AttendanceRecorded($attendance->load('employee')));

        return response()->json([
            'message' => 'Attendance recorded successfully!',
            'attendance' => $attendance->load('employee'),
        ], 201);
    }
}
