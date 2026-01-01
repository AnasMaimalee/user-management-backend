<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Exports\AttendanceReportExport;
use App\Models\DailyAttendance;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendanceExportController extends Controller
{
    // Employee: My Attendance PDF
    public function myPdf(Request $request)
    {
        $from = $request->query('from', now()->subDays(30)->format('Y-m-d'));
        $to = $request->query('to', now()->format('Y-m-d'));

        $records = DailyAttendance::with(['employee'])
            ->where('employee_id', auth()->user()->employee_id)
            ->whereBetween('attendance_date', [$from, $to])
            ->orderBy('attendance_date')
            ->get();

        if ($records->isEmpty()) {
            abort(404, 'No attendance records found');
        }

        $data = [
            'records' => $records,
            'from' => $from,
            'to' => $to,
            'title' => 'My Attendance Report',
            'generated_at' => now()->format('d F Y, H:i'),
        ];

        $pdf = Pdf::loadView('exports.attendance-pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('My-Attendance-' . now()->format('Y-m-d') . '.pdf');
    }

    // Employee: My Attendance Excel
    public function myExcel(Request $request)
    {
        $from = $request->query('from', now()->subDays(30)->format('Y-m-d'));
        $to = $request->query('to', now()->format('Y-m-d'));

        return Excel::download(
            new AttendanceReportExport($from, $to, null, auth()->user()->employee_id),
            'My-Attendance-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    // Admin: Full Attendance PDF
    public function pdf(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'department_id' => 'nullable|exists:departments,id',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        $records = DailyAttendance::with(['employee.department'])
            ->whereBetween('attendance_date', [$request->from, $request->to]);

        if ($request->filled('employee_id')) {
            $records->where('employee_id', $request->employee_id);
        }

        if ($request->filled('department_id')) {
            $records->whereHas('employee', fn($q) => $q->where('department_id', $request->department_id));
        }

        $records = $records->orderBy('attendance_date')->get();

        if ($records->isEmpty()) {
            abort(404, 'No attendance records found');
        }

        $data = [
            'records' => $records,
            'from' => $request->from,
            'to' => $request->to,
            'title' => 'Attendance Report',
            'generated_at' => now()->format('d F Y, H:i'),
        ];

        $pdf = Pdf::loadView('exports.attendance-pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('Attendance-Report-' . now()->format('Y-m-d') . '.pdf');
    }

    // Admin: Full Attendance Excel
    public function excel(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'department_id' => 'nullable|exists:departments,id',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        return Excel::download(
            new AttendanceReportExport(
                $request->from,
                $request->to,
                $request->department_id,
                $request->employee_id
            ),
            'Attendance-Report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
