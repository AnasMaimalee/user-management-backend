<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\Employee;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\PayslipMail;

class PayrollController extends Controller
{
    // Admin: Get all payrolls for a month/year
    public function index(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        return Payroll::with('employee')
            ->where('year', $request->year)
            ->where('month', $request->month)
            ->get();
    }

    // Admin: Run payroll for a month
    public function run(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $employees = Employee::all();

        $payrolls = [];
        foreach ($employees as $employee) {
            $basic = $employee->basic_salary ?? 0; // Assume added to Employee model
            $allowances = $employee->allowances ?? 0;
            $deductions = $employee->deductions ?? 0;
            $savings = $employee->savings_deduction ?? 0; // For wallet
            $net = $basic + $allowances - $deductions - $savings;

            $payroll = Payroll::create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'employee_id' => $employee->id,
                'basic_salary' => $basic,
                'allowances' => $allowances,
                'deductions' => $deductions,
                'savings_deduction' => $savings,
                'net_salary' => $net,
                'year' => $request->year,
                'month' => $request->month,
                'status' => 'processed',
            ]);

            // Generate payslip PDF
            $pdf = Pdf::loadView('emails.payslip', ['payroll' => $payroll]);
            $path = 'payslips/' . $payroll->id . '.pdf';
            Storage::disk('public')->put($path, $pdf->output());
            $payroll->payslip_path = $path;
            $payroll->save();

            // Send email with payslip
            Mail::to($employee->email)->send(new PayslipMail($payroll));

            $payrolls[] = $payroll;
        }

        return response()->json([
            'message' => 'Payroll run successfully for ' . count($payrolls) . ' employees',
            'payrolls' => $payrolls,
        ], 201);
    }

    // Employee: Get my payslips
    public function myPayslips()
    {
        return Payroll::where('employee_id', auth()->user()->employee_id)
            ->latest()
            ->get();
    }

    // Download payslip
    public function downloadPayslip(Payroll $payroll)
    {
        if ($payroll->employee_id !== auth()->user()->employee_id && !auth()->user()->can('view payroll')) {
            abort(403);
        }

        return Storage::disk('public')->download($payroll->payslip_path, 'payslip-' . $payroll->month . '-' . $payroll->year . '.pdf');
    }
}
