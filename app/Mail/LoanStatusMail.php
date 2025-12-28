<?php


namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Loan;

class LoanStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public Loan $loan;
    public string $statusMessage;

    public function __construct(Loan $loan, string $statusMessage)
    {
        $this->loan = $loan;
        $this->statusMessage = $statusMessage;
    }

    public function build()
    {
        return $this->subject("Loan Request Status: {$this->statusMessage}")
            ->view('emails.loan_status');
    }
}
