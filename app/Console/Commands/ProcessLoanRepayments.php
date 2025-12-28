<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Loan;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;

class ProcessLoanRepayments extends Command
{
    protected $signature = 'loans:repay';
    protected $description = 'Process monthly loan repayments';

    public function handle(PaymentService $paymentService)
    {
        $loans = Loan::where('status', 'approved')
            ->where('remaining_amount', '>', 0)
            ->get();

        foreach ($loans as $loan) {

            DB::transaction(function () use ($loan, $paymentService) {

                // Skip if wallet has no money
                if ($loan->employee->wallet->balance < $loan->monthly_deduction) {
                    return;
                }

                $paymentService->debit(
                    $loan->employee->wallet,
                    $loan->monthly_deduction,
                    'Loan repayment',
                    $loan->id
                );

                $loan->remaining_amount -= $loan->monthly_deduction;

                if ($loan->remaining_amount <= 0) {
                    $loan->remaining_amount = 0;
                    $loan->status = 'completed';
                }

                $loan->save();
            });
        }

        $this->info('Loan repayments processed successfully.');
    }
}
