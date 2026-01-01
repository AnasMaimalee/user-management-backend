<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Exports\AttendanceReportExport;
use App\Models\DailyAttendance;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class AttendanceExportController extends Controller
{
    public function excel(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'department_id' => 'nullable|uuid'
        ]);

        return Excel::download(
            new AttendanceReportExport(
                $request->from,
                $request->to,
                $request->department_id
            ),
            'attendance-report.xlsx'
        );
    }

    public function pdf(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'department_id' => 'nullable|uuid'
        ]);

        $query = DailyAttendance::query()
            ->with(['employee.department'])
            ->whereBetween('attendance_date', [$request->from, $request->to]);

        // ✅ FILTER BY DEPARTMENT (CORRECT WAY)
        if ($request->department_id) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        $data = $query
            ->orderBy('attendance_date')
            ->get();

        $pdf = PDF::loadView('exports.attendance', [
            'data' => $data,
            'from' => $request->from,
            'to' => $request->to,
        ]);

        return $pdf->download('attendance-report.pdf');
    }

}
