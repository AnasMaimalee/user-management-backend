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
            'status' => 'required|string',
            'worked_minutes' => 'nullable|integer',
            'late_minutes' => 'nullable|integer',
        ]);

        $employee = Employee::findOrFail($request->employee_id);

        // CRITICAL CHECK: Only allow clock-in if fingerprint enrolled
        if (!$employee->fingerprint_enrolled_at) {
            return response()->json([
                'message' => 'Biometric enrollment required. Please complete fingerprint enrollment before clocking in.'
            ], 403);
        }

        // Optional: Prevent double clock-in on same day
        $today = Carbon::today();
        $existing = DailyAttendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Attendance already recorded for today.',
                'attendance' => $existing
            ], 409);
        }

        $attendance = DailyAttendance::create([
            'employee_id' => $employee->id,
            'attendance_date' => $today,
            'status' => $request->status ?? 'present',
            'worked_minutes' => $request->worked_minutes ?? 0,
            'late_minutes' => $request->late_minutes ?? 0,
            'clock_in' => now(),
            // clock_out can be set later if needed
        ]);

        // Broadcast realtime update
        event(new AttendanceRecorded($attendance->load('employee')));

        return response()->json([
            'message' => 'Attendance recorded successfully!',
            'attendance' => $attendance->load('employee')
        ], 201);
    }
}
