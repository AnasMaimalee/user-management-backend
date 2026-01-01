<?php

namespace App\Http\Controllers\Api\Payroll;

use App\Http\Controllers\Controller;
use App\Mail\PayslipMail;
use App\Models\Employee;
use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PayrollController extends Controller
{
    /**
     * Admin: Get all payrolls for a specific month/year
     */
    public function index(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $payrolls = Payroll::with('employee')
            ->where('year', $request->year)
            ->where('month', $request->month)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($payrolls);
    }

    /**
     * Admin: Run payroll for a month/year
     */
    public function run(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $year = $request->year;
        $month = $request->month;

        // Prevent duplicate payroll run
        $existing = Payroll::where('year', $year)->where('month', $month)->exists();
        if ($existing) {
            return response()->json([
                'message' => "Payroll for {$month}/{$year} has already been processed."
            ], 422);
        }

        $employees = Employee::where('status', 'active')->get();

        if ($employees->isEmpty()) {
            return response()->json([
                'message' => 'No active employees found to process payroll.'
            ], 400);
        }

        $processedCount = 0;
        $failedEmployees = [];

        foreach ($employees as $employee) {
            try {
                $basic = $employee->basic_salary ?? 0;
                $allowances = $employee->allowances ?? 0;
                $deductions = $employee->deductions ?? 0;
                $savings = $employee->monthly_savings ?? 0;

                $gross = $basic + $allowances;
                $totalDeductions = $deductions + $savings;
                $net = $gross - $totalDeductions;

                $payroll = Payroll::create([
                    'id' => (string) Str::uuid(),
                    'employee_id' => $employee->id,
                    'basic_salary' => $basic,
                    'allowances' => $allowances,
                    'deductions' => $deductions,
                    'savings_deduction' => $savings,
                    'net_salary' => max(0, $net),
                    'year' => $year,
                    'month' => $month,
                    'status' => 'processed',
                ]);

                // Handle loan deduction
                $activeLoan = $employee->loans()
                    ->where('status', 'approved')
                    ->where('remaining_amount', '>', 0)
                    ->first();

                if ($activeLoan && $net > 0) {
                    $deductAmount = min($activeLoan->monthly_deduction, $net);
                    $activeLoan->deductPayment($deductAmount);
                }

                // Generate and save payslip PDF
                $pdf = Pdf::loadView('emails.payslip', ['payroll' => $payroll]);
                $filename = "payslip-{$employee->employee_code}-{$month}-{$year}.pdf";
                $path = "payslips/{$payroll->id}.pdf";
                Storage::disk('public')->put($path, $pdf->output());

                $payroll->update(['payslip_path' => $path]);

                // Queue email (doesn't block if fails)
                Mail::to($employee->email)->queue(new PayslipMail($payroll));

                // Handle monthly savings → wallet
                if ($savings > 0) {
                    $wallet = $employee->wallet ?? $employee->wallet()->create([
                        'id' => (string) Str::uuid(),
                        'balance' => 0,
                    ]);

                    $wallet->addTransaction(
                        amount: $savings,
                        type: 'deposit',
                        description: "Monthly savings - {$month}/{$year}"
                    );
                }

                $processedCount++;

            } catch (\Exception $e) {
                $failedEmployees[] = $employee->employee_code;
                \Log::error("Payroll failed for employee {$employee->id}: " . $e->getMessage());
                // Continue to next employee
                continue;
            }
        }

        $message = "Payroll processed for {$year}-{$month}. ";
        $message .= "{$processedCount} employees successful.";

        if (!empty($failedEmployees)) {
            $message .= " Failed for: " . implode(', ', $failedEmployees);
        }

        return response()->json([
            'message' => $message,
            'processed' => $processedCount,
            'failed' => count($failedEmployees),
            'failed_employees' => $failedEmployees,
        ], 201);
    }


    /**
     * Employee: Get my payslips
     */
    public function myPayslips()
    {
        $payslips = Payroll::where('employee_id', auth()->user()->employee_id)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        return response()->json($payslips);
    }

    /**
     * Download payslip PDF
     */
    public function downloadPayslip(Payroll $payroll)
    {
        // Security check
        if ($payroll->employee_id !== auth()->user()->employee_id &&
            !auth()->user()->hasRole('admin|hr')) {
            abort(403, 'Unauthorized');
        }

        if (!$payroll->payslip_path || !Storage::disk('public')->exists($payroll->payslip_path)) {
            abort(404, 'Payslip not found');
        }

        $monthName = \Carbon\Carbon::createFromDate($payroll->year, $payroll->month, 1)->format('F');
        $filename = "Payslip-{$payroll->employee->employee_code}-{$monthName}-{$payroll->year}.pdf";

        return Storage::disk('public')->download($payroll->payslip_path, $filename);
    }


}
