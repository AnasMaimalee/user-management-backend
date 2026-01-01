<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\DailyAttendance;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::all();
        $today = Carbon::today();

        foreach ($employees as $employee) {
            // Biometric enrollment seeding
            if (rand(0, 1)) {
                $employee->update([
                    'device_user_id' => rand(1000, 9999),
                    'fingerprint_enrolled_at' => now()->subDays(rand(1, 60)),
                ]);
            } elseif (rand(0, 1)) {
                $employee->update([
                    'device_user_id' => rand(1000, 9999),
                    'fingerprint_enrolled_at' => null,
                ]);
            }

            // Create today's attendance record
            DailyAttendance::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'attendance_date' => $today,
                ],
                [
                    'clock_in' => $today->copy()->setHour(9)->addMinutes(rand(0, 120)),
                    'clock_out' => $today->copy()->setHour(17)->addMinutes(rand(0, 120)),
                    'worked_minutes' => rand(360, 540),
                    'late_minutes' => rand(0, 60),
                    'status' => fake()->randomElement(['present', 'present', 'present', 'late', 'absent']),
                ]
            );
        }
    }
}
