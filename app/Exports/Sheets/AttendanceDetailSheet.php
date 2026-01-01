<?php

namespace App\Exports\Sheets;

use App\Models\Employee;
use App\Models\DailyAttendance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AttendanceDetailSheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(
        protected string $from,
        protected string $to,
        protected ?string $departmentId
    ) {}

    public function title(): string
    {
        return 'Attendance Details';
    }

    public function collection(): Collection
    {
        $from = Carbon::parse($this->from);
        $to   = Carbon::parse($this->to);

        $period = CarbonPeriod::create($from, $to);

        $employees = Employee::with('department')
            ->when($this->departmentId, fn ($q) =>
            $q->where('department_id', $this->departmentId)
            )
            ->get();

        $attendances = DailyAttendance::whereBetween(
            'attendance_date',
            [$from->toDateString(), $to->toDateString()]
        )
            ->whereIn('status', ['present', 'late'])
            ->get()
            ->groupBy(fn ($a) =>
                $a->employee_id . '|' . $a->attendance_date
            );

        $rows = collect();

        foreach ($employees as $employee) {
            foreach ($period as $date) {
                $key = $employee->id . '|' . $date->toDateString();
                $attendance = $attendances->get($key)?->first();

                if (! $attendance) {
                    continue; // ❗ ONLY PRESENT
                }

                $rows->push([
                    'Employee'     => $employee->first_name . ' ' . $employee->last_name,
                    'Department'   => $employee->department->name ?? '—',
                    'Date'         => $date->toDateString(),
                    'Status'       => ucfirst($attendance->status),
                    'Clock In'     => $attendance->clock_in,
                    'Clock Out'    => $attendance->clock_out,
                    'Worked (min)' => $attendance->worked_minutes,
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Employee',
            'Department',
            'Date',
            'Status',
            'Clock In',
            'Clock Out',
            'Worked (min)',
        ];
    }
}
