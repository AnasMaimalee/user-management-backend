<?php

namespace App\Jobs;

use App\Exports\AttendanceReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExportAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $from,
        protected string $to,
        protected ?string $departmentId,
        protected string $path
    ) {}

    public function handle(): void
    {
        Excel::store(
            new AttendanceReportExport($this->from, $this->to, $this->departmentId),
            $this->path
        );
    }
}
