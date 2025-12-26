<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    // Employee: View my wallet
    public function myWallet()
    {
        $wallet = auth()->user()->employee->wallet()->with('transactions')->firstOrCreate([
            'employee_id' => auth()->user()->employee_id,
        ], [
            'id' => (string) Str::uuid(),
            'balance' => 0,
            'monthly_savings' => auth()->user()->employee->monthly_savings ?? 0,
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

        $wallet = auth()->user()->employee->wallet;

        if (!$wallet || $wallet->balance < $request->amount) {
            return response()->json(['message' => 'Insufficient balance'], 422);
        }

        $transaction = $wallet->addTransaction(
            amount: $request->amount,
            type: 'withdrawal',
            description: $request->reason,
            status: 'pending'
        );

        return response()->json([
            'message' => 'Withdrawal request submitted. Awaiting approval.',
            'transaction' => $transaction,
        ], 201);
    }

    // Admin: Get all pending withdrawals
    public function pendingWithdrawals()
    {
        $transactions = WalletTransaction::with(['wallet.employee'])
            ->where('type', 'withdrawal')
            ->where('status', 'pending')
            ->latest()
            ->get();

        return response()->json($transactions);
    }

    // Admin: Approve/Reject withdrawal
    public function processWithdrawal(Request $request, WalletTransaction $transaction)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'note' => 'nullable|string|max:500',
        ]);

        if ($transaction->status !== 'pending' || $transaction->type !== 'withdrawal') {
            return response()->json(['message' => 'Invalid transaction'], 422);
        }

        if ($request->action === 'approve') {
            $transaction->status = 'approved';
            $transaction->processed_by = auth()->id();
            $transaction->processed_at = now();
            $transaction->wallet->balance -= $transaction->amount;
            $transaction->wallet->save();
        } else {
            $transaction->status = 'rejected';
            $transaction->processed_by = auth()->id();
            $transaction->processed_at = now();
        }

        $transaction->description .= "\nAdmin Note: " . ($request->note ?? 'No note');
        $transaction->save();

        return response()->json([
            'message' => "Withdrawal request {$request->action}d",
            'transaction' => $transaction,
        ]);
    }
}
