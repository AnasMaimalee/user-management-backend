<?php

namespace App\Exports;

use App\Models\DailyAttendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceReportExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    protected string $from;
    protected string $to;
    protected ?string $departmentId = null;
    protected ?string $employeeId = null; // Added for employee filter

    public function __construct(
        string $from,
        string $to,
        ?string $departmentId = null,
        ?string $employeeId = null
    ) {
        $this->from = $from;
        $this->to = $to;
        $this->departmentId = $departmentId;
        $this->employeeId = $employeeId;
    }

    public function collection()
    {
        $query = DailyAttendance::with(['employee.department'])
            ->whereBetween('attendance_date', [$this->from, $this->to]);

        if ($this->employeeId) {
            $query->where('employee_id', $this->employeeId);
        }

        if ($this->departmentId) {
            $query->whereHas('employee', function ($q) {
                $q->where('department_id', $this->departmentId);
            });
        }

        return $query->orderBy('attendance_date')->get();
    }

    public function map($row): array
    {
        return [
            $row->employee->first_name . ' ' . $row->employee->last_name,
            $row->employee->department->name ?? '-',
            $row->attendance_date,
            strtoupper($row->status ?? 'absent'),
            $row->clock_in ? \Carbon\Carbon::parse($row->clock_in)->format('H:i') : '-',
            $row->clock_out ? \Carbon\Carbon::parse($row->clock_out)->format('H:i') : '-',
        ];
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
