<?php

namespace Database\Seeders;

use App\Models\Payroll;
use Illuminate\Database\Seeder;

class PayrollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generate 12 months of payroll for each existing employee (last year + current)
        $employees = \App\Models\Employee::all();

        foreach ($employees as $employee) {
            // Last year: 12 months
            for ($month = 1; $month <= 12; $month++) {
                Payroll::factory()->create([
                    'employee_id' => $employee->id,
                    'year' => 2024,
                    'month' => $month,
                    'status' => 'paid',
                ]);
            }

            // Current year: up to current month
            $currentMonth = now()->month;
            for ($month = 1; $month <= $currentMonth; $month++) {
                Payroll::factory()->create([
                    'employee_id' => $employee->id,
                    'year' => now()->year,
                    'month' => $month,
                    'status' => $month < $currentMonth ? 'paid' : 'processed',
                ]);
            }
        }

        // Extra random payroll records for variety
        Payroll::factory(50)->create();
    }
}
