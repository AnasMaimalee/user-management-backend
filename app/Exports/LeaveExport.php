<?php

namespace App\Exports;

use App\Models\LeaveRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeaveExport implements FromCollection, WithHeadings, WithMapping
{
    protected $leaves;
    protected $type;

    public function __construct($leaves, $type)
    {
        $this->leaves = $leaves;
        $this->type = $type;
    }

    public function collection()
    {
        return $this->leaves;
    }

    public function headings(): array
    {
        return [
            '#',
            'Employee',
            'Email',
            'Department',
            'Branch',
            'Rank',
            'Start Date',
            'End Date',
            'Resume Date',
            'Reason',
            'Status',
            'Applied On',
        ];
    }

    public function map($leave): array
    {
        static $index = 0;
        $index++;

        // SAFE EMPLOYEE NAME - This is the fix
        $employeeName = 'Unknown Employee';
        $employeeEmail = 'N/A';
        $department = 'N/A';
        $branch = 'N/A';
        $rank = 'N/A';

        if ($leave->employee) {
            $employeeName = trim(($leave->employee->first_name ?? '') . ' ' . ($leave->employee->last_name ?? ''));
            $employeeEmail = $leave->employee->email ?? 'N/A';
            $department = $leave->employee->department?->name ?? 'N/A';
            $branch = $leave->employee->branch?->name ?? 'N/A';
            $rank = $leave->employee->rank?->name ?? 'N/A';
        }

        return [
            $index,
            $employeeName ?: 'Unknown Employee',
            $employeeEmail,
            $department,
            $branch,
            $rank,
            $leave->start_date,
            $leave->end_date,
            $leave->resume_date ?? '—',
            $leave->reason ?? '—',
            ucfirst($leave->status),
            $leave->created_at->format('M d, Y'),
        ];
    }
}
