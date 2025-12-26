<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayrollFactory extends Factory
{
    protected $model = Payroll::class;

    public function definition(): array
    {
        $employee = Employee::inRandomOrder()->first() ?? Employee::factory()->create();

        $basic = $this->faker->numberBetween(150000, 800000);
        $allowances = $this->faker->numberBetween(20000, 150000);
        $deductions = $this->faker->numberBetween(10000, 80000);
        $savings = $this->faker->numberBetween(0, 100000);

        $net = $basic + $allowances - $deductions - $savings;

        return [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'employee_id' => $employee->id,
            'basic_salary' => $basic,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'savings_deduction' => $savings,
            'net_salary' => $net,
            'year' => $this->faker->year,
            'month' => $this->faker->month,
            'status' => $this->faker->randomElement(['draft', 'processed', 'paid']),
            'payslip_path' => $this->faker->optional(0.8)->word . '.pdf', // 80% chance of having payslip
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
