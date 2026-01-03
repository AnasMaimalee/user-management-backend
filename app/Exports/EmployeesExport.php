<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class EmployeesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $employees;

    public function __construct(Collection $employees)
    {
        $this->employees = $employees;
    }

    public function collection()
    {
        return $this->employees;
    }

    public function headings(): array
    {
        return [
            '#',
            'Code',
            'First Name',
            'Last Name',
            'Email',
            'Department',
            'Rank',
            'Branch',
            'Status',
        ];
    }

    public function map($employee): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            $employee->employee_code ?? '—',
            $employee->first_name ?? '—',
            $employee->last_name ?? '—',
            $employee->email ?? '—',
            $employee->department?->name ?? '—',
            $employee->rank?->name ?? '—',
            $employee->branch?->name ?? '—',
            ucfirst($employee->status),
        ];
    }
}
