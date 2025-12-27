<?php

namespace App\Mail;

use App\Models\WalletTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WalletWithdrawalNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $transaction;
    public $note;

    public function __construct(WalletTransaction $transaction, $note = null)
    {
        $this->transaction = $transaction;
        $this->note = $note;
    }

    public function envelope(): Envelope
    {
        $status = $this->transaction->status === 'approved' ? 'Approved' : 'Rejected';
        return new Envelope(
            subject: "Withdrawal Request {$status} - Maimalee HR"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.wallet-withdrawal',
            with: [
                'transaction' => $this->transaction,
                'note' => $this->note,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
