<?php

namespace App\Mail;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoanStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $loan;
    public $status;

    /**
     * Create a new message instance.
     */
    public function __construct(Loan $loan, string $status)
    {
        $this->loan = $loan;
        $this->status = $status;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->status === 'approved'
            ? 'Your Loan Request Has Been Approved!'
            : 'Update on Your Loan Request';

        return $this->subject($subject)
            ->view('emails.loan-status')
            ->with([
                'loan'   => $this->loan,
                'status' => $this->status,
            ]);
    }
}
