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
        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date|after_or_equal:from',
        ]);

        $from = $request->from ?? now()->subDays(30)->format('Y-m-d');
        $to   = $request->to   ?? now()->format('Y-m-d');

        $records = DailyAttendance::with(['employee'])
            ->where('employee_id', auth()->user()->employee_id)
            ->whereBetween('attendance_date', [$from, $to])
            ->orderBy('attendance_date')
            ->get();

        if ($records->isEmpty()) {
            abort(404, 'No attendance records found for the selected period');
        }

        $data = [
            'records'      => $records,
            'from'         => $from,
            'to'           => $to,
            'title'        => 'My Attendance Report',
            'generated_at' => now()->format('d F Y, H:i'),
        ];

        $pdf = Pdf::loadView('exports.attendance-pdf', $data)
            ->setPaper('a4', 'landscape');

        $filename = 'My-Attendance';
        if ($request->from && $request->to) {
            $filename .= "_{$request->from}_to_{$request->to}";
        }
        $filename .= '.pdf';

        return $pdf->download($filename);
    }

    public function myExcel(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date|after_or_equal:from',
        ]);

        $from = $request->from ?? now()->subDays(30)->format('Y-m-d');
        $to   = $request->to   ?? now()->format('Y-m-d');

        $filename = 'My-Attendance';
        if ($request->from && $request->to) {
            $filename .= "_{$request->from}_to_{$request->to}";
        }
        $filename .= '-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new AttendanceReportExport($from, $to, null, auth()->user()->employee_id),
            $filename
        );
    }

    // Admin: Full Attendance PDF
    public function pdf(Request $request)
    {
        $request->validate([
            'from'          => 'nullable|date',
            'to'            => 'nullable|date|after_or_equal:from',
            'department_id' => 'nullable|exists:departments,id',
            'employee_id'   => 'nullable|exists:employees,id',
        ]);

        $from = $request->from ?? now()->subDays(31)->format('Y-m-d');
        $to   = $request->to   ?? now()->format('Y-m-d');

        $records = DailyAttendance::with(['employee.department'])
            ->whereBetween('attendance_date', [$from, $to]);

        if ($request->filled('employee_id')) {
            $records->where('employee_id', $request->employee_id);
        }

        if ($request->filled('department_id')) {
            $records->whereHas('employee', fn($q) => $q->where('department_id', $request->department_id));
        }

        $records = $records->orderBy('attendance_date')->get();

        if ($records->isEmpty()) {
            abort(404, 'No attendance records found for the selected period');
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
            'from'          => 'nullable|date',
            'to'            => 'nullable|date|after_or_equal:from',
            'department_id' => 'nullable|exists:departments,id',
            'employee_id'   => 'nullable|exists:employees,id',
        ]);

        // Use provided dates or fallback to last 31 days
        $from = $request->from ?? now()->subDays(31)->format('Y-m-d');
        $to   = $request->to   ?? now()->format('Y-m-d');

        // Build the query (same logic as PDF)
        $query = DailyAttendance::with(['employee.department'])
            ->whereBetween('attendance_date', [$from, $to]);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        $records = $query->orderBy('attendance_date')->get();

        if ($records->isEmpty()) {
            abort(404, 'No attendance records found for the selected period');
        }

        // Export using the same export class
        return Excel::download(
            new AttendanceReportExport($from, $to, $request->department_id, $request->employee_id),
            'Attendance-Report-' . $from . '_to_' . $to . '-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
