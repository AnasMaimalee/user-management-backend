<?php

namespace App\Exports;

use App\Models\DailyAttendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AttendanceReportExport implements FromCollection, WithHeadings
{
    protected $from;
    protected $to;

    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to   = $to;
    }

    public function collection()
    {
        return DailyAttendance::with('employee')
            ->whereBetween('attendance_date', [$this->from, $this->to])
            ->get()
            ->map(function ($row) {
                return [
                    'Employee ID'   => $row->employee->employee_no ?? '',
                    'Employee Name' => $row->employee->first_name . ' ' . $row->employee->last_name,
                    'Date'          => $row->attendance_date,
                    'Status'        => ucfirst($row->status),
                    'Worked (mins)' => $row->worked_minutes,
                    'Late (mins)'   => $row->late_minutes,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Employee ID',
            'Employee Name',
            'Date',
            'Status',
            'Worked (mins)',
            'Late (mins)',
        ];
    }
}
