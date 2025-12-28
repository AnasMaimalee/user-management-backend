<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\Employee;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\PayslipMail;
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
            'year' => 'required|integer',
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
        $payrolls = [];

        foreach ($employees as $employee) {
            $basic = $employee->basic_salary ?? 0;
            $allowances = $employee->allowances ?? 0;
            $deductions = $employee->deductions ?? 0;
            $savings = $employee->monthly_savings ?? 0; // For wallet

            $net = $basic + $allowances - $deductions - $savings;

            $payroll = Payroll::create([
                'id' => (string) Str::uuid(),
                'employee_id' => $employee->id,
                'basic_salary' => $basic,
                'allowances' => $allowances,
                'deductions' => $deductions,
                'savings_deduction' => $savings,
                'net_salary' => max(0, $net), // Prevent negative
                'year' => $year,
                'month' => $month,
                'status' => 'processed',
            ]);

            // Handle active loans
            $activeLoan = $employee->loans()->where('status', 'approved')->where('remaining_amount', '>', 0)->first();

            if ($activeLoan) {
                $deduct = min($activeLoan->monthly_deduction, $net); // Don't go negative
                $activeLoan->deductPayment($deduct);
                $net -= $deduct;

                // Add transaction to wallet if exists
                if ($employee->wallet) {
                    $employee->wallet->addTransaction(
                        $deduct,
                        'withdrawal',
                        "Loan repayment deduction"
                    );
                }
            }

            // Generate PDF payslip using correct template
            $pdf = Pdf::loadView('emails.payslip', ['payroll' => $payroll]);
            $filename = "payslip-{$employee->employee_code}-{$month}-{$year}.pdf";
            $path = 'payslips/' . $payroll->id . '.pdf';

            Storage::disk('public')->put($path, $pdf->output());
            $payroll->payslip_path = $path;
            $payroll->save();

            // Send email with attached payslip
            Mail::to($employee->email)->send(new PayslipMail($payroll));

            // Add savings to wallet if applicable
            if ($savings > 0) {
                $wallet = $employee->wallet ?? Wallet::create([
                    'id' => (string) Str::uuid(),
                    'employee_id' => $employee->id,
                    'monthly_savings' => $savings,
                ]);

                $wallet->addTransaction(
                    amount: $savings,
                    type: 'deposit',
                    description: "Monthly savings from payroll {$month}/{$year}"
                );
            }

            $payrolls[] = $payroll;
        }

        return response()->json([
            'message' => "Payroll successfully processed for {$year}-{$month}. " . count($payrolls) . ' payslips generated and emailed.',
            'count' => count($payrolls),
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
