<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LoanExport implements FromCollection, WithHeadings, WithMapping
{
    protected Collection $loans;

    public function __construct($loans)
    {
        $this->loans = collect($loans);
    }

    public function collection()
    {
        return $this->loans;
    }

    public function headings(): array
    {
        return [
            'ID', 'Employee', 'Department', 'Amount', 'Months', 'Monthly Deduction',
            'Remaining', 'Paid', 'Status', 'Reason', 'Updated At'
        ];
    }

    public function map($loan): array
    {
        return [
            $loan->id,
            $loan->employee->first_name . ' ' . $loan->employee->last_name,
            $loan->employee->department->name ?? '—',
            '₦' . number_format($loan->amount),
            $loan->months,
            '₦' . number_format($loan->monthly_deduction),
            '₦' . number_format($loan->remaining_amount),
            '₦' . number_format($loan->paid_amount),
            ucfirst($loan->status),
            $loan->reason ?? '—',
            $loan->updated_at->format('d M Y'),
        ];
    }
}
