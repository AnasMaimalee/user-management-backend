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
            'to'   => 'required|date|after_or_equal:from',
        ]);

        return Excel::download(
            new AttendanceReportExport($request->from, $request->to),
            'attendance-report.xlsx'
        );
    }

    public function pdf(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        $data = DailyAttendance::with('employee')
            ->whereBetween('attendance_date', [$request->from, $request->to])
            ->orderBy('attendance_date')
            ->get();

        $pdf = PDF::loadView('exports.attendance', [
            'data' => $data,
            'from' => $request->from,
            'to'   => $request->to,
        ]);

        return $pdf->download('attendance-report.pdf');
    }
}
