<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DailyAttendance;
use App\Models\Employee;
class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::all();

        foreach ($employees as $employee) {
            // 50% chance fully enrolled
            if (rand(0, 1)) {
                $employee->update([
                    'biometric_uid' => rand(1000, 9999),
                    'fingerprint_enrolled_at' => now()->subDays(rand(1, 60)),
                ]);
            }
            // 30% chance sent but pending
            elseif (rand(0, 1)) {
                $employee->update([
                    'biometric_uid' => rand(1000, 9999),
                    'fingerprint_enrolled_at' => null,
                ]);
            }
            // 20% not sent
        }
    }
}
