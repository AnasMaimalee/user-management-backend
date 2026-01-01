<?php

namespace App\Exports;

use App\Models\DailyAttendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AttendanceReportExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(
        protected string $from,
        protected string $to,
        protected ?string $departmentId = null
    ) {}

    public function collection()
    {
        $query = DailyAttendance::query()
            ->with(['employee.department'])
            ->whereBetween('attendance_date', [$this->from, $this->to]);

        // ✅ FILTER BY DEPARTMENT (CRITICAL)
        if ($this->departmentId) {
            $query->whereHas('employee', function ($q) {
                $q->where('department_id', $this->departmentId);
            });
        }

        return $query
            ->orderBy('attendance_date')
            ->get()
            ->map(function ($row) {
                return [
                    'Employee Name' =>
                        $row->employee->first_name . ' ' . $row->employee->last_name,

                    'Department' =>
                        $row->employee->department->name ?? '-',

                    'Date' => $row->attendance_date,

                    'Status' => strtoupper($row->status),

                    'Clock In' => $row->clock_in ?? '-',

                    'Clock Out' => $row->clock_out ?? '-',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Employee Name',
            'Department',
            'Date',
            'Status',
            'Clock In',
            'Clock Out',
        ];
    }
}
