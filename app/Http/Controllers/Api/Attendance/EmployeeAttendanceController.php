<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Services\Attendance\AttendanceSummaryService;
use Illuminate\Http\Request;

class EmployeeAttendanceController extends Controller
{
    public function summary(Request $request, AttendanceSummaryService $service)
    {
        $employee = $request->user()->employee;

        return response()->json(
            $service->monthlySummary($employee)
        );
    }

    public function history(Request $request)
    {
        $employee = $request->user()->employee;

        return response()->json(
            $employee->dailyAttendances()
                ->latest('attendance_date')
                ->paginate(30)
        );
    }
}
