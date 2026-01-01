<?php

namespace App\Exports\Sheets;

use App\Models\DailyAttendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AttendanceSummarySheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(
        protected string $from,
        protected string $to,
        protected ?string $departmentId
    ) {}

    public function title(): string
    {
        return 'Summary';
    }

    public function collection(): Collection
    {
        $from = Carbon::parse($this->from);
        $to   = Carbon::parse($this->to);

        $employees = Employee::with('department')
            ->when($this->departmentId, fn ($q) =>
            $q->where('department_id', $this->departmentId)
            )
            ->get();

        $attendance = DailyAttendance::whereBetween(
            'attendance_date',
            [$from->toDateString(), $to->toDateString()]
        )
            ->whereIn('status', ['present', 'late'])
            ->get()
            ->groupBy('employee_id');

        $rows = collect();

        foreach ($employees as $employee) {
            $records = $attendance->get($employee->id, collect());

            if ($records->isEmpty()) {
                continue;
            }

            $rows->push([
                'Employee'       => $employee->first_name . ' ' . $employee->last_name,
                'Department'     => $employee->department->name ?? '—',
                'Days Present'   => $records->count(),
                'Total Minutes'  => $records->sum('worked_minutes'),
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Employee',
            'Department',
            'Days Present',
            'Total Minutes',
        ];
    }
}
