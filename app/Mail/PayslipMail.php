<?php

namespace App\Mail;

use App\Models\Payroll;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class PayslipMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payroll;

    public function __construct(Payroll $payroll)
    {
        $this->payroll = $payroll;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Payslip for ' . date('F Y', mktime(0, 0, 0, $this->payroll->month, 1, $this->payroll->year)),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payslip-email', // Custom email body
            with: ['payroll' => $this->payroll],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath(storage_path('app/public/' . $this->payroll->payslip_path))
                ->as('payslip.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
