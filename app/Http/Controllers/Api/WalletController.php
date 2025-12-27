<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Mail\WalletWithdrawalNotification;
use Illuminate\Support\Facades\Mail;

class WalletController extends Controller
{
    // Employee: View my wallet
    public function myWallet()
    {
        $user = auth()->user();

        // If user has no employee record (e.g., pure admin), return empty wallet
        if (!$user->employee) {
            return response()->json([
                'balance' => 0,
                'monthly_savings' => 0,
                'goal_name' => null,
                'goal_amount' => 0,
                'goal_target_date' => null,
                'transactions' => [],
            ]);
        }

        $wallet = $user->employee->wallet()->with('transactions')->firstOrCreate([
            'employee_id' => $user->employee_id,
        ], [
            'id' => (string) Str::uuid(),
            'balance' => 0,
            'monthly_savings' => $user->employee->monthly_savings ?? 0,
        ]);

        return response()->json($wallet->load('transactions'));
    }

    // Employee: Request withdrawal
    public function requestWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'reason' => 'required|string|min:10|max:500',
        ]);

        $employee = auth()->user()->employee;
        $wallet = $employee->wallet;

        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found'], 404);
        }

        if ($wallet->balance < $request->amount) {
            return response()->json(['message' => 'Insufficient balance'], 422);
        }

        $transaction = $wallet->addTransaction(
            amount: $request->amount,
            type: 'withdrawal',
            description: "Withdrawal request: " . $request->reason,
            status: 'pending'
        );

        return response()->json([
            'message' => 'Withdrawal request submitted successfully. Awaiting HR approval.',
            'transaction' => $transaction->load('wallet.employee'),
        ], 201);
    }

    // Admin: Get all pending withdrawals
    public function pendingWithdrawals()
    {
        $transactions = WalletTransaction::with(['wallet.employee'])
            ->where('type', 'withdrawal')
            ->where('status', 'pending')
            ->latest()
            ->paginate(20); // Better for large systems

        return response()->json($transactions);
    }

    // Admin: Approve/Reject withdrawal
    public function processWithdrawal(Request $request, WalletTransaction $transaction)
    {
        $request->validate([
            'action' => ['required', Rule::in(['approve', 'reject'])],
            'note' => 'nullable|string|max:500',
        ]);

        if ($transaction->status !== 'pending' || $transaction->type !== 'withdrawal') {
            return response()->json(['message' => 'This transaction cannot be processed'], 422);
        }

        $action = $request->action;
        $note = $request->note ? "\nAdmin Note: " . trim($request->note) : '';

        if ($action === 'approve') {
            if ($transaction->wallet->balance < $transaction->amount) {
                return response()->json(['message' => 'Insufficient balance to approve'], 422);
            }

            $transaction->status = 'approved';
            $transaction->wallet->decrement('balance', $transaction->amount);
        } else {
            $transaction->status = 'rejected';
        }

        $transaction->processed_by = auth()->id();
        $transaction->processed_at = now();
        $transaction->description .= $note;
        $transaction->save();
        Mail::to($transaction->wallet->employee->email)->send(
            new WalletWithdrawalNotification($transaction, $request->note)
        );
        return response()->json([
            'message' => "Withdrawal request has been {$action}d",
            'transaction' => $transaction->fresh()->load('wallet.employee'),
        ]);
    }

    // Employee: Set savings goal
    public function setGoal(Request $request)
    {
        $request->validate([
            'goal_name' => 'required|string|max:100',
            'goal_amount' => 'required|numeric|min:1000',
            'goal_target_date' => 'required|date|after:today',
        ]);

        $wallet = auth()->user()->employee->wallet;

        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found'], 404);
        }

        $wallet->update([
            'goal_name' => $request->goal_name,
            'goal_amount' => $request->goal_amount,
            'goal_target_date' => $request->goal_target_date,
        ]);

        return response()->json([
            'message' => 'Savings goal updated successfully!',
            'wallet' => $wallet,
        ]);
    }

    // Admin: Manual deposit (e.g., bonus)
    public function manualDeposit(Request $request, $employeeId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'description' => 'required|string|max:500',
        ]);

        $wallet = Wallet::where('employee_id', $employeeId)->firstOrFail();

        $wallet->addTransaction(
            amount: $request->amount,
            type: 'deposit',
            description: "Manual deposit: " . $request->description,
            status: 'approved'
        );

        return response()->json([
            'message' => 'Deposit added successfully',
            'wallet' => $wallet->fresh(),
        ]);
    }
}
