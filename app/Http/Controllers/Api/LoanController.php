<?php

namespace App\Http\Controllers\Api;
use App\Services\PaymentService;
use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\LoanStatusMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
    public function myLoans()
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json([
                'message' => 'Employee record not found'
            ], 404);
        }

        $loans = $employee->loans()->latest()->get();

        return response()->json($loans);
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
            'note' => 'nullable|string|max:500',
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

        // ✅ SEND EMAIL AFTER SUCCESSFUL TRANSACTION
        Mail::to($loan->employee->email)->send(
            new LoanStatusMail($loan)
        );

        return response()->json([
            'message' => "Loan {$loan->status} successfully",
            'loan' => $loan,
        ]);
    }

    // Example usage in your repayment logic
    public function repayLoan(Loan $loan)
    {
        $this->paymentService->debit(
            $loan->employee->wallet,
            $loan->monthly_deduction,
            'Loan repayment',
            $loan->id
        );

        $loan->remaining_amount -= $loan->monthly_deduction;
        if ($loan->remaining_amount <= 0) {
            $loan->status = 'completed';
            $loan->remaining_amount = 0;
        }
        $loan->save();
    }

}
