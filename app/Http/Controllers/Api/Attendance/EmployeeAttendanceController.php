<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Services\Attendance\AttendanceSummaryService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;
use App\Models\DailyAttendance;
class EmployeeAttendanceController extends Controller
{
    // app/Http/Controllers/Api/Attendance/EmployeeAttendanceController.php

    public function today()
    {
        $user = auth()->user();
        $today = Carbon::today()->toDateString();

        $attendance = DailyAttendance::with('employee')
            ->where('employee_id', $user->employee->id) // assuming user has employee relation
            ->whereDate('attendance_date', $today)
            ->first();

        return response()->json($attendance ?? [
            'attendance_date' => $today,
            'status' => 'absent',
            'clock_in' => null,
            'clock_out' => null,
            'worked_minutes' => 0,
        ]);
    }
    public function summary(Request $request)
    {
        $user = auth()->user();

        $query = DailyAttendance::where('employee_id', $user->employee->id);

        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('attendance_date', $request->month)
                ->whereYear('attendance_date', $request->year);
        } elseif ($request->filled('year')) {
            $query->whereYear('attendance_date', $request->year);
        } elseif ($request->filled('month')) {
            $query->whereMonth('attendance_date', $request->month);
        }

        $records = $query->get();

        $summary = [
            'present_days' => $records->where('status', 'present')->count(),
            'late_days'    => $records->where('status', 'late')->count(),
            'absent_days'  => $records->where('status', 'absent')->count(),
            'on_leave_days'=> $records->where('status', 'on_leave')->count(),
            'holiday_days' => $records->where('status', 'holiday')->count(),
        ];

        return response()->json($summary);
    }

    public function history(Request $request)
    {
        $user = auth()->user();

        $query = DailyAttendance::with('employee')
            ->where('employee_id', $user->employee->id)
            ->orderBy('attendance_date', 'desc');

        // Apply filters if provided
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('attendance_date', $request->month)
                ->whereYear('attendance_date', $request->year);
        } elseif ($request->filled('year')) {
            $query->whereYear('attendance_date', $request->year);
        } elseif ($request->filled('month')) {
            $query->whereMonth('attendance_date', $request->month);
        }

        $history = $query->get();

        return response()->json([
            'data' => $history
        ]);
    }
    public function exportPdf(Request $request)
    {
        $user = auth()->user();

        $query = DailyAttendance::where('user_id', $user->id);

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('attendance_date', [$request->from, $request->to]);
        }

        $attendance = $query->latest()->get();

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('exports.attendance', [
            'attendance' => $attendance,
            'title' => 'My Attendance Report',
            'date' => now()->format('F j, Y'),
        ]);

        return $pdf->download('my_attendance_' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $user = auth()->user();

        $query = DailyAttendance::where('user_id', $user->id);

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('attendance_date', [$request->from, $request->to]);
        }

        $attendance = $query->latest()->get();

        return Excel::download(new AttendanceExport($attendance), 'my_attendance_' . now()->format('Y-m-d') . '.xlsx');
    }
}
