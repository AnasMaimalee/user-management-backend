<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class PaymentService
{
    /**
     * Credit money into a wallet
     */
    public function credit(Wallet $wallet, float $amount, string $reason, ?string $reference = null)
    {
        if ($amount <= 0) {
            throw new Exception('Invalid credit amount');
        }

        return DB::transaction(function () use ($wallet, $amount, $reason, $reference) {

            // Update balance
            $wallet->balance += $amount;
            $wallet->save();

            // Record transaction
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'deposit',
                'amount' => $amount,
                'reason' => $reason,
                'reference' => $reference,
                'status' => 'completed',
            ]);

            return $wallet;
        });
    }

    /**
     * Deduct money from a wallet
     */
    public function debit(Wallet $wallet, float $amount, string $reason, ?string $reference = null)
    {
        if ($amount <= 0) {
            throw new Exception('Invalid debit amount');
        }

        if ($wallet->balance < $amount) {
            throw new Exception('Insufficient wallet balance');
        }

        return DB::transaction(function () use ($wallet, $amount, $reason, $reference) {

            // Update balance
            $wallet->balance -= $amount;
            $wallet->save();

            // Record transaction
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'amount' => $amount,
                'reason' => $reason,
                'reference' => $reference,
                'status' => 'completed',
            ]);

            return $wallet;
        });
    }
}
