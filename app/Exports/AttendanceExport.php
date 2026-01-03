<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    protected $attendance;

    public function __construct(Collection $attendance)
    {
        $this->attendance = $attendance;
    }

    public function collection()
    {
        return $this->attendance;
    }

    public function headings(): array
    {
        return [
            '#',
            'Date',
            'Status',
            'Check In',
            'Check Out',
        ];
    }

    public function map($record): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            \Carbon\Carbon::parse($record->attendance_date)->format('M d, Y'),
            ucfirst($record->status ?? 'Absent'),
            $record->clock_in ? \Carbon\Carbon::parse($record->clock_in)->format('h:i A') : '—',
            $record->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('h:i A') : '—',
        ];
    }
}
