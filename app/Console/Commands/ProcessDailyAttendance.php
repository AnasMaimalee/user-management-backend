<?php

// app/Console/Commands/ProcessDailyAttendance.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Services\Attendance\AttendanceProcessor;
use Carbon\Carbon;

class ProcessDailyAttendance extends Command
{
    protected $signature = 'attendance:process-daily {date?}';
    protected $description = 'Process daily attendance for all employees';

    public function handle()
    {
        $date = $this->argument('date')
            ? Carbon::parse($this->argument('date'))
            : Carbon::yesterday();

        $processor = app(AttendanceProcessor::class);

        Employee::chunk(50, function ($employees) use ($processor, $date) {
            foreach ($employees as $employee) {
                $processor->processEmployeeForDate($employee, $date);
            }
        });

        $this->info("Attendance processed for {$date->toDateString()}");
    }
}
