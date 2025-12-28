<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\LoanStatusMail;
use Illuminate\Support\Str;

class LoanController extends Controller
{
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

    // Admin: Process loan (approve or reject)

    public function processLoan(Request $request, Loan $loan)
    {

        $request->validate([
            'action' => 'required|in:approve,reject',
            'note' => 'nullable|string|max:500',
        ]);

        if ($loan->status !== 'pending') {
            return response()->json([
                'message' => 'Loan already processed'
            ], 422);
        }

        $loan->status = $request->action === 'approve' ? 'approved' : 'rejected';
        $loan->approved_by = auth()->id();
        $loan->admin_note = $request->note;

        if ($request->action === 'approve') {
            $loan->approved_at = now();
        }

        $loan->save();

        // Email user
        Mail::to($loan->employee->email)->send(
            new LoanStatusMail($loan, $loan->status)
        );

        return response()->json([
            'message' => "Loan {$loan->status} successfully",
            'loan' => $loan
        ]);
    }
}
