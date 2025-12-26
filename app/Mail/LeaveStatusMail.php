<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $leave;

    /**
     * Create a new message instance.
     */
    public function __construct(LeaveRequest $leave)
    {
        $this->leave = $leave;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Leave Request Has Been ' . ucfirst($this->leave->status),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.leave-status', // ← Correct view name
            with: [
                'leave' => $this->leave,
                'employee' => $this->leave->employee,
                'reviewer' => $this->leave->reviewer, // assuming you have reviewed_by relationship
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
