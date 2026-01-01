<?php

namespace App\Http\Controllers\Api\Loan;
use App\Http\Controllers\Controller;
use App\Mail\LoanStatusMail;
use App\Models\Loan;
use App\Models\Wallet;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LoanController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    // ================= Employee Routes =================

    // Employee: Request a loan
    public function requestLoan(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000|max:5000000',
            'months' => 'required|integer|min:3|max:36',
            'reason' => 'required|string|min:20|max:1000',
        ]);

        $loan = Loan::create([
            'id' => (string) Str::uuid(),
            'employee_id' => auth()->user()->employee_id,
            'amount' => $request->amount,
            'months' => $request->months,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Loan request submitted. Awaiting approval.',
            'loan' => $loan,
        ], 201);
    }

    // Employee: View my loans
    public function myLoans(Request $request)
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $query = $employee->loans()->latest();

        // Add the same filters as above...
        if ($request->filled('year')) { /* ... */ }
        // etc.

        return response()->json($query->get());
    }

    // ================= Admin Routes =================

    // Admin: List pending loans
    public function pendingLoans()
    {

        $loans = Loan::with('employee')
            ->where('status', 'pending')
            ->latest()
            ->get();

        return response()->json($loans);
    }


    public function processLoan(Request $request, Loan $loan)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'note'   => 'nullable|string|max:500',
        ]);

        if ($loan->status !== 'pending') {
            return response()->json(['message' => 'Loan already processed'], 422);
        }

        DB::transaction(function () use ($request, $loan) {
            if ($request->action === 'approve') {
                $loan->monthly_deduction = $loan->amount / $loan->months;
                $loan->remaining_amount = $loan->amount;
                $loan->approved_at = now();

                // CREDIT WALLET
                app(PaymentService::class)->credit(
                    $loan->employee->wallet,
                    $loan->amount,
                    'Loan approved',
                    $loan->id
                );

                $loan->status = 'approved';
            } else {
                $loan->status = 'rejected';
            }

            $loan->approved_by = auth()->id();
            $loan->admin_note = $request->note;
            $loan->save();
        });

        // SEND EMAIL — FIXED: Pass both loan and status/action
        try {
            Mail::to($loan->employee->email)->send(
                new LoanStatusMail($loan, $loan->status)  // ← Now passing 2 arguments
            );
        } catch (\Exception $e) {
            \Log::warning('Failed to send loan status email', [
                'loan_id' => $loan->id,
                'employee_email' => $loan->employee->email,
                'error' => $e->getMessage()
            ]);
            // Don't fail the response just because email failed
        }

        return response()->json([
            'message' => "Loan {$loan->status} successfully",
            'loan'    => $loan->load('employee'),
        ]);
    }

    public function repayLoan(Loan $loan)
    {
        $companyWallet = Wallet::where('is_company', true)->first();

        // Debit employee wallet
        $this->paymentService->debit(
            $loan->employee->wallet,
            $loan->monthly_deduction,
            'Loan repayment',
            $loan->id
        );

        // Credit company wallet
        $this->paymentService->credit(
            $companyWallet,
            $loan->monthly_deduction,
            'Loan repayment from ' . $loan->employee->first_name,
            $loan->id
        );

        // Update loan remaining
        $loan->remaining_amount -= $loan->monthly_deduction;
        if ($loan->remaining_amount <= 0) {
            $loan->status = 'completed';
            $loan->remaining_amount = 0;
        }

        $loan->save();
    }
    /**
     * Get filtered loan history (admin + filtered view)
     * Supports year/month/employee/department filters
     */
    private function applyFilters($query, Request $request)
    {
        if ($request->filled('year')) {
            $year = $request->year;
            $query->where(function ($q) use ($year) {
                $q->whereYear('created_at', $year)
                    ->orWhereYear('approved_at', $year)
                    ->orWhereYear('updated_at', $year);
            });
        }

        if ($request->filled('month') && $request->filled('year')) {
            $month = $request->month;
            $query->where(function ($q) use ($month) {
                $q->whereMonth('created_at', $month)
                    ->orWhereMonth('approved_at', $month)
                    ->orWhereMonth('updated_at', $month);
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }
    }

    public function pending(Request $request)
    {
        $query = Loan::with(['employee.department'])
            ->where('status', 'pending')
            ->latest('created_at');

        $this->applyFilters($query, $request);

        $loans = $query->get();

        $loans->transform(fn($loan) => $loan->append('paid_amount'));

        return response()->json($loans);
    }

    public function history(Request $request)
    {
        $query = Loan::with(['employee.department'])
            ->whereNot('status', 'pending') // ONLY processed loans
            ->latest('updated_at');

        $this->applyFilters($query, $request);

        $loans = $query->get();

        $loans->transform(fn($loan) => $loan->append('paid_amount'));

        return response()->json($loans);
    }

}
