<?php

namespace App\Exports;

use App\Models\Payroll;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PayrollExport implements FromCollection, WithHeadings, WithMapping
{
    protected $year;
    protected $month;

    public function __construct($year, $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function collection()
    {
        return Payroll::with('employee')
            ->where('year', $this->year)
            ->where('month', $this->month)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Employee Code',
            'Name',
            'Basic Salary',
            'Allowances',
            'Deductions',
            'Savings',
            'Net Salary',
            'Status'
        ];
    }

    public function map($payroll): array
    {
        return [
            $payroll->employee->employee_code ?? 'N/A',
            $payroll->employee->first_name . ' ' . $payroll->employee->last_name,
            $payroll->basic_salary,
            $payroll->allowances,
            $payroll->deductions,
            $payroll->savings_deduction,
            $payroll->net_salary,
            ucfirst($payroll->status)
        ];
    }
}
