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
    public $statusMessage;

    public function __construct(Loan $loan, string $statusMessage)
    {
        $this->loan = $loan;
        $this->statusMessage = $statusMessage;
    }

    public function build()
    {
        return $this->subject('Your Loan Request Status')
            ->view('emails.loan_status') // Create this blade
            ->with([
                'loan' => $this->loan,
                'statusMessage' => $this->statusMessage,
            ]);
    }
}
