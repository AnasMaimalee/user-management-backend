<?php

namespace App\Mail;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmployeeWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $employee;
    public $password;
    public $loginUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Employee $employee, string $plainPassword)
    {
        $this->employee = $employee;
        $this->password = $plainPassword;
        $this->loginUrl = config('app.frontend_url', 'http://localhost:5173') . '/login';
        // Or hardcode: $this->loginUrl = 'http://localhost:5173/login';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Maimalee HR Portal Login Credentials',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.employee-welcome',
            with: [
                'employee' => $this->employee,
                'password' => $this->password,
                'loginUrl' => $this->loginUrl,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
